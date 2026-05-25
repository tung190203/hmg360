<?php

return [
    'name' => 'Quản lý user',
    'slug' => 'user',
    'view_namespace' => 'ttxt-website',
    'menu' => [
        'title' => 'Quản lý user',
        'icon' => 'fas fa-user-cog',
        'section' => 'systems',
        'sort_order' => 120,
        'items' => [
            'users' => [
                'title' => 'Users',
                'route' => 'backend_user',
            ],
            'groups' => [
                'title' => 'Groups',
                'route' => 'backend_group',
            ],
        ],
    ],
    'permissions' => ['view', 'create', 'update', 'delete'],
];
