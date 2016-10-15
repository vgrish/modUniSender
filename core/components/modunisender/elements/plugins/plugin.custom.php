<?php
/** @var $scriptProperties */
/** @var modunisender $modunisender */
if (!$modunisender = $modx->getService('modunisender')) {
    return;
}
$modunisender->initialize($modx->context->key);

/** @var modX $modx */
switch ($modx->event->name) {

    case 'msOnChangeOrderStatus':

        $status = $modx->getOption('status', $scriptProperties);

        /** @var msOrder $order */
        /** @var modUser $user */
        /** @var modUserProfile $profile */
        if (
            !$order = $modx->getOption('order', $scriptProperties)
            OR
            !$user = $order->getOne('User')
            OR
            !$profile = $order->getOne('UserProfile')
            OR
            !$items = $order->getMany('Products')
        ) {
            return;
        }

        $fields = array(
            'email' => $profile->get('email'),
            'phone' => $profile->get('phone'),
            'city'  => $profile->get('city'),
            'name'  => $user->get('username'),
        );

        /* get phone by email */
        $response = $modunisender->uniSenderExportContacts(array(
            'field_names' => array('phone'),
            'email'       => $profile->get('email')
        ));
        $phone = !empty($response['data']) ? $response['data'][0][0] : '';
        if (!empty($phone)) {
            /* delete old phone from all list */
            $modunisender->uniSenderImportContacts(array(
                'field_names' => array(
                    'phone',
                    'delete'
                ),
                'data'        => array(
                    array(
                        $phone,
                        1
                    )
                ),
            ));
        }

        /* update email && phone on "user_create" list */
        $modunisender->uniSenderImportContacts(array(
            'field_names' => array(
                'email',
                'phone',
                'email_list_ids',
                'phone_list_ids',
            ),
            'data'        => array(
                array(
                    $profile->get('email'),
                    $profile->get('phone'),
                    $modunisender->getOption('addressbook_user_create', null),
                    $modunisender->getOption('addressbook_user_create', null),
                )
            ),
        ));

        /* get all list for email */
        $response = $modunisender->uniSenderExportContacts(array(
            'field_names' => array('email_list_ids'),
            'email'       => $profile->get('email')
        ));
        $addBooks = !empty($response['data']) ? $response['data']['0'] : array();
        $excludeBooks = array();


        /** @var msOrderProduct $item */
        foreach ($items as $item) {
            /** @var msProduct $product */
            if (!$product = $item->getOne('Product')) {
                continue;
            }

            $name = $product->get('pagetitle');

            $q = $modx->newQuery('modTemplateVar');
            $q->leftJoin('modTemplateVarResource', 'modTemplateVarResource',
                'modTemplateVarResource.tmplvarid = modTemplateVar.id');
            $q->where(array(
                'modTemplateVar.name'              => 'adrbook_unisender',
                'modTemplateVarResource.contentid' => $product->get('id')
            ));
            $q->select('modTemplateVarResource.value');
            if ($q->prepare() AND $q->stmt->execute()) {
                if (!$name = $modx->getValue($q->stmt)) {
                    $name = $product->get('pagetitle');
                }
            }

            $book = $modunisender->uniSenderGetListIdFromName($name, true);

            switch (true) {
                case $book AND $status == 1 AND $order->get('cost') == 0:
                case $book AND $status == 2:
                    $addBooks[] = $book;
                    break;
                case $book AND $status == 4:
                    $excludeBooks[] = $book;
                    break;
                default:
                    break;
            }
        }

        if (!empty($excludeBooks)) {
            /* Exclude */
            $modunisender->uniSenderExclude(array(
                'list_ids' => $modunisender->cleanAndImplode($excludeBooks),
                'contact'  => $profile->get('email')
            ));
        }

        if (!empty($addBooks)) {
            /* Subscribe */
            $modunisender->uniSenderSubscribe(array(
                'list_ids' => $modunisender->cleanAndImplode($addBooks),
                'fields'   => $fields
            ));
        }

        if ($modx->context->key == 'mgr' AND !empty($modx->event->_output)) {
            $response = array(
                'success' => true,
                'message' => '',
                'data'    => array(),
            );
            echo $modx->toJSON($response);
            exit;
        }

        break;
}