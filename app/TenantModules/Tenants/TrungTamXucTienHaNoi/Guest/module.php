<?php

return [
    'name' => 'Người dùng',
    'slug' => 'guest',
    'view_namespace' => 'ttxt-leads',
    'menu' => [
        'title' => 'Người dùng',
        'icon' => 'fas fa-users',
        'route' => 'tenant.trung_tam_xuc_tien_ha_noi.guest.index',
        'section' => 'content',
        'sort_order' => 60,
    ],
    'permissions' => ['view', 'create', 'update', 'delete'],
];
