<?php

/**
 * Class modunisenderMainController
 */
abstract class modunisenderMainController extends modExtraManagerController
{
    /** @var modunisender $modunisender */
    public $modunisender;


    /**
     * @return void
     */
    public function initialize()
    {
        $corePath = $this->modx->getOption('modunisender_core_path', null,
            $this->modx->getOption('core_path') . 'components/modunisender/');
        require_once $corePath . 'model/modunisender/modunisender.class.php';

        $this->modunisender = new modunisender($this->modx);
        $this->addCss($this->modunisender->config['cssUrl'] . 'mgr/main.css');
        $this->addJavascript($this->modunisender->config['jsUrl'] . 'mgr/modunisender.js');
        $this->addHtml('
		<script type="text/javascript">
			modunisender.config = ' . $this->modx->toJSON($this->modunisender->config) . ';
			modunisender.config.connector_url = "' . $this->modunisender->config['connectorUrl'] . '";
		</script>
		');

        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return array('modunisender:default');
    }


    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }
}


/**
 * Class IndexManagerController
 */
class IndexManagerController extends modunisenderMainController
{

    /**
     * @return string
     */
    public static function getDefaultController()
    {
        return 'home';
    }
}