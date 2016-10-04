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
            'email'    => $profile->get('email'),
            'phone'    => $profile->get('mobilephone'),
            'city'     => $profile->get('city'),
            'username' => $user->get('username'),
        );


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
                'modTemplateVar.name'              => 'adrbook_sendpulse',
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
                    $modunisender->uniSenderSubscribe(array(
                        'list_ids' => $book,
                        'fields'   => $fields
                    ));
                    break;
                case $book AND $status == 4:
                    $modunisender->uniSenderExclude(array(
                        'list_ids' => $book,
                        'contact'  => $profile->get('email')
                    ));
                    break;
                default:
                    break;
            }
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