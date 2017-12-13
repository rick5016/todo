<?php

// name => module/ctrl#action
// Défault : '/' => '' (module 'common' par défaut et ctrl 'index' par défaut et action 'index' par défaut
// Défault : '/' => 'action' (module 'common' par défaut et ctrl 'index' par défaut)
// Défault : '/' => 'ctrl#action' (module 'common' par défaut)
$routes = array(
    '/'           => '',
    '/task'       => 'front/inbox#task',
    '/inbox'      => 'front/inbox#index',
    '/calendrier' => 'front/calendrier#index',
    '/delete'     => 'front/inbox#delete',
    '/performe'   => 'front/inbox#performe',
    '/cancel'     => 'front/inbox#cancel',
);
