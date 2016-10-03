<?php


ini_set('display_errors', 1);
ini_set('error_reporting', -1);


/**
 * The base class for modunisender.
 *
 * https://www.unisender.com/ru/support/integration/api
 *
 */
class modunisender
{
    /* @var modX $modx */
    public $modx;

    /** @var mixed|null $namespace */
    public $namespace = 'modunisender';
    /** @var array $config */
    public $config = array();
    /** @var array $initialized */
    public $initialized = array();

    /**
     * @param modX  $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = array())
    {
        $this->modx =& $modx;

        $corePath = $this->getOption('core_path', $config,
            $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/modunisender/');
        $assetsPath = $this->getOption('assets_path', $config,
            $this->modx->getOption('assets_path', null, MODX_ASSETS_PATH) . 'components/modunisender/');
        $assetsUrl = $this->getOption('assets_url', $config,
            $this->modx->getOption('assets_url', null, MODX_ASSETS_URL) . 'components/modunisender/');
        $connectorUrl = $assetsUrl . 'connector.php';

        $this->config = array_merge(array(
            'namespace'       => $this->namespace,
            'connectorUrl'    => $connectorUrl,
            'assetsBasePath'  => MODX_ASSETS_PATH,
            'assetsBaseUrl'   => MODX_ASSETS_URL,
            'assetsPath'      => $assetsPath,
            'assetsUrl'       => $assetsUrl,
            'actionUrl'       => $assetsUrl . 'action.php',
            'cssUrl'          => $assetsUrl . 'css/',
            'jsUrl'           => $assetsUrl . 'js/',
            'corePath'        => $corePath,
            'modelPath'       => $corePath . 'model/',
            'processorsPath'  => $corePath . 'processors/',
            'templatesPath'   => $corePath . 'elements/templates/mgr/',
            'jsonResponse'    => true,
            'prepareResponse' => true,
            'showLog'         => false,
        ), $config);

        $this->modx->addPackage('modunisender', $this->getOption('modelPath'));
        $this->modx->lexicon->load('modunisender:default');
        $this->namespace = $this->getOption('namespace', $config, 'modunisender');
    }

    /**
     * @param       $n
     * @param array $p
     */
    public function __call($n, array$p)
    {
        echo __METHOD__ . ' says: ' . $n;
    }

    /**
     * @param       $key
     * @param array $config
     * @param null  $default
     *
     * @return mixed|null
     */
    public function getOption($key, $config = array(), $default = null, $skipEmpty = false)
    {
        $option = $default;
        if (!empty($key) AND is_string($key)) {
            if ($config != null AND array_key_exists($key, $config)) {
                $option = $config[$key];
            } elseif (array_key_exists($key, $this->config)) {
                $option = $this->config[$key];
            } elseif (array_key_exists("{$this->namespace}_{$key}", $this->modx->config)) {
                $option = $this->modx->getOption("{$this->namespace}_{$key}");
            }
        }
        if ($skipEmpty AND empty($option)) {
            $option = $default;
        }

        return $option;
    }

    /**
     * @param string $ctx
     * @param array  $scriptProperties
     *
     * @return bool|mixed
     */
    public function initialize($ctx = 'web', array $scriptProperties = array())
    {
        if (isset($this->initialized[$ctx])) {
            return $this->initialized[$ctx];
        }

        $this->modx->error->reset();
        $this->config = array_merge($this->config, $scriptProperties, array('ctx' => $ctx));

        if ($ctx != 'mgr' AND (!defined('MODX_API_MODE') OR !MODX_API_MODE)) {

        }

        $load = true;
        $this->initialized[$ctx] = $load;

        return $load;
    }


    /**
     * @param string $message
     * @param array  $data
     * @param bool   $showLog
     * @param bool   $writeLog
     */
    public function log($message = '', $data = array(), $showLog = false)
    {
        if ($showLog OR $this->getOption('showLog', null, false, true)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, $message);
            if (!empty($data)) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, print_r($data, 1));
            }
        }
    }

    public function uniSenderGetLists(array $params = array())
    {
        $mode = '/getLists/';
        $params = array_merge(array(), $params);
        $data = $this->request($mode, $params, 'POST');

        return $data;
    }
    

    /**
     * @param string $mode
     *
     * @return mixed|null|string
     */
    protected function uniSenderApiUrl($mode = '')
    {
        $url = $this->getOption('api_url', null, 'https://api.unisender.com', true);
        $lang = $this->getOption('api_lang', null, 'ru', true);

        $url = rtrim($url, '/') . '/' . $lang . '/api/' . $mode;

        return $url;
    }

    /**
     * @param string $modexw
     * @param null   $params
     * @param string $url
     *
     * @return array|mixed
     */
    public function request($mode = '', $params = null, $method = 'POST', $format = 'json', $url = '')
    {
        $mode = trim($mode, ' / ');

        if (empty($url)) {
            $url = $this->uniSenderApiUrl($mode);
        }

        $params['api_key'] = $this->getOption('api_key', null);
        if (!empty($format)) {
            $params['format'] = $format;
        }

        $ch = curl_init();

        $method = strtoupper($method);
        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, count($params));
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                break;
            default:
                if (!empty($params)) {
                    $url .= '?' . http_build_query($params);
                }
        }

        curl_setopt_array(
            $ch,
            array(
                CURLOPT_URL            => $url,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_HEADER         => 0
            )
        );

        $data = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if (in_array($code, array('401', '500'))) {
            $this->log('Error', $data, true);
            $data = array();
        } else {
            $data = json_decode($data, true);
        }
        $this->log('', $data);

        return $data;
    }


}