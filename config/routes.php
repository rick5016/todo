<?php

// name => module/ctrl#action
// Défault : '/' => '' (module 'common' par défaut et ctrl 'index' par défaut et action 'index' par défaut
// Défault : '/' => 'action' (module 'common' par défaut et ctrl 'index' par défaut)
// Défault : '/' => 'ctrl#action' (module 'common' par défaut)
$routes = array(
    '/'           => '',
    '/task'       => 'front/index#task',
    '/inbox'      => 'front/index#inbox',
    '/calendrier' => 'front/index#calendrier',
    '/delete'     => 'front/index#delete',
    '/done'       => 'front/index#done',
    '/cancel'     => 'front/index#cancel',
);
