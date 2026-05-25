<?php

return [
    'name' => 'Navigation',
    'slug' => 'menu',
    'view_namespace' => 'ttxt-content',
    'menu' => [
        'title' => 'Navigation',
        'icon' => 'fas fa-bars',
        'route' => 'backend_menu',
        'section' => 'content',
        'sort_order' => 80,
    ],
    'permissions' => ['view', 'create', 'update', 'delete'],
];
