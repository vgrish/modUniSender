<?php

$settings = array();

$tmp = array(

    'api_url'  => array(
        'value' => 'https://api.unisender.com',
        'xtype' => 'textfield',
        'area'  => 'modunisender_main',
    ),
    'api_key'  => array(
        'value' => '',
        'xtype' => 'textfield',
        'area'  => 'modunisender_main',
    ),


    'addressbook_user_create'    => array(
        'value' => '',
        'xtype' => 'textfield',
        'area'  => 'modunisender_addressbooks',
    ),
    'addressbook_user_pay_order' => array(
        'value' => '',
        'xtype' => 'textfield',
        'area'  => 'modunisender_addressbooks',
    ),

    //временные
    /* 'assets_path'                => array(
         'value' => '{base_path}modunisender/assets/components/modunisender/',
         'xtype' => 'textfield',
         'area'  => 'modunisender_temp',
     ),
     'assets_url'                 => array(
         'value' => '/modunisender/assets/components/modunisender/',
         'xtype' => 'textfield',
         'area'  => 'modunisender_temp',
     ),
     'core_path'                  => array(
         'value' => '{base_path}modunisender/core/components/modunisender/',
         'xtype' => 'textfield',
         'area'  => 'modunisender_temp',
     )*/

    /*
	'some_setting' => array(
		'xtype' => 'combo-boolean',
		'value' => true,
		'area' => 'modunisender_main',
	),
	*/
);

foreach ($tmp as $k => $v) {
    /* @var modSystemSetting $setting */
    $setting = $modx->newObject('modSystemSetting');
    $setting->fromArray(array_merge(
        array(
            'key'       => 'modunisender_' . $k,
            'namespace' => PKG_NAME_LOWER,
        ), $v
    ), '', true, true);

    $settings[] = $setting;
}

unset($tmp);
return $settings;
