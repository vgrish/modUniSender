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
        if ($status != 2) {
            return;
        }

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
            !$book = $modunisender->getOption('addressbook_user_pay_order', null)
        ) {
            return;
        }


        $fields = array(
            'email'    => $profile->get('email'),
            'phone'    => $profile->get('mobilephone'),
            'city'     => $profile->get('city'),
            'username' => $user->get('username'),
        );

        $modunisender->uniSenderSubscribe(array(
            'list_ids' => $book,
            'fields'   => $fields
        ));

        break;
}