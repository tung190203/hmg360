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
                'route' => 'tenant.trung_tam_xuc_tien_ha_noi.user.users.index',
            ],
            'groups' => [
                'title' => 'Groups',
                'route' => 'tenant.trung_tam_xuc_tien_ha_noi.user.groups.index',
            ],
        ],
    ],
    'permissions' => ['view', 'create', 'update', 'delete'],
];
