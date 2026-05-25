<?php

return [
    'name' => 'Popup',
    'slug' => 'popup',
    'view_namespace' => 'ttxt-content',
    'menu' => [
        'title' => 'Popup',
        'icon' => 'fas fa-window-restore',
        'route' => 'backend_popup',
        'section' => 'content',
        'sort_order' => 70,
    ],
    'permissions' => ['view', 'create', 'update', 'delete'],
];
