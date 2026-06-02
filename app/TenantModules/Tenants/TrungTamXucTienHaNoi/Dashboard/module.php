<?php

return [
    'name' => 'Dashboard',
    'slug' => 'dashboard',
    'view_namespace' => 'ttxt-dashboard',
    'menu' => [
        'title' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'route' => 'tenant.trung_tam_xuc_tien_ha_noi.dashboard.index',
        'section' => 'content',
        'sort_order' => 10,
    ],
    'permissions' => ['view'],
];
