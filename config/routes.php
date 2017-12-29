<?php

// name => module/ctrl#action
// Défault : '/' => '' (module 'common' par défaut et ctrl 'index' par défaut et action 'index' par défaut
// Défault : '/' => 'action' (module 'common' par défaut et ctrl 'index' par défaut)
// Défault : '/' => 'ctrl#action' (module 'common' par défaut)
$routes = array(
    '/'                   => '',
    '/projects'           => 'common/index#projects',
    '/projectscolor'      => 'common/index#projectscolor',
    '/projectsactivation' => 'common/index#projectsactivation',
    '/task'               => 'front/inbox#task',
    '/inbox'              => 'front/inbox#index',
    '/calendrier'         => 'front/calendrier#index',
    '/day'                => 'front/calendrier#day',
    '/delete'             => 'front/inbox#delete',
    '/performe'           => 'front/inbox#performe',
    '/cancel'             => 'front/inbox#cancel',
    '/ia'                 => 'common/index#ia',
);