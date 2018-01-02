<?php

// name => module/ctrl#action (joue l'action du template : common/index#index)
// name => array('module/ctrl#action', 0) (ne joue pas l'action du template)
$routes = array(
    '/'                   => 'common/index#index',
    '/login'              => 'common/index#login',
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