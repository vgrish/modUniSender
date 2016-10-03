<?php

/**
 * The home manager controller for modunisender.
 *
 */
class modunisenderHomeManagerController extends modunisenderMainController
{
    /* @var modunisender $modunisender */
    public $modunisender;


    /**
     * @param array $scriptProperties
     */
    public function process(array $scriptProperties = array())
    {
    }


    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('modunisender');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->modunisender->config['cssUrl'] . 'mgr/main.css');
        $this->addCss($this->modunisender->config['cssUrl'] . 'mgr/bootstrap.buttons.css');
        $this->addJavascript($this->modunisender->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addJavascript($this->modunisender->config['jsUrl'] . 'mgr/widgets/items.grid.js');
        $this->addJavascript($this->modunisender->config['jsUrl'] . 'mgr/widgets/items.windows.js');
        $this->addJavascript($this->modunisender->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addJavascript($this->modunisender->config['jsUrl'] . 'mgr/sections/home.js');
        $this->addHtml('<script type="text/javascript">
		Ext.onReady(function() {
			MODx.load({ xtype: "modunisender-page-home"});
		});
		</script>');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->modunisender->config['templatesPath'] . 'home.tpl';
    }
}