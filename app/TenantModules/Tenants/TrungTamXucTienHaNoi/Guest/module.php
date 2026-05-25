<?php

return [
    'name' => 'Người dùng',
    'slug' => 'guest',
    'view_namespace' => 'ttxt-leads',
    'menu' => [
        'title' => 'Người dùng',
        'icon' => 'fas fa-users',
        'route' => 'backend_guest',
        'section' => 'content',
        'sort_order' => 60,
    ],
    'permissions' => ['view', 'create', 'update', 'delete'],
];
