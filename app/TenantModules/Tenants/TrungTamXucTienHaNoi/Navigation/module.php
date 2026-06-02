<?php

return [
    'name' => 'Navigation',
    'slug' => 'menu',
    'view_namespace' => 'ttxt-content',
    'menu' => [
        'title' => 'Navigation',
        'icon' => 'fas fa-bars',
        'route' => 'tenant.trung_tam_xuc_tien_ha_noi.menu.index',
        'section' => 'content',
        'sort_order' => 80,
    ],
    'permissions' => ['view', 'create', 'update', 'delete'],
];
