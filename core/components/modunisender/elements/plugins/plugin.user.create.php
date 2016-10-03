<?php

/** @var $scriptProperties */
/** @var modunisender $modunisender */
if (!$modunisender = $modx->getService('modunisender')) {
    return;
}
$modunisender->initialize($modx->context->key);

/** @var modX $modx */
switch ($modx->event->name) {

    case 'OnUserSave':

        $mode = $modx->getOption('mode', $scriptProperties);
        if ($mode != modSystemEvent::MODE_NEW) {
            return;
        }

        /** @var modUser $user */
        if (
            !$user = $modx->getOption('user', $scriptProperties)
            OR
            !$profile = $user->getOne('Profile')
            OR
            !$book = $modunisender->getOption('addressbook_user_create', null)
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