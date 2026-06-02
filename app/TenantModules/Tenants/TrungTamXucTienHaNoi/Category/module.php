<?php

return [
    'name' => 'Danh mục',
    'slug' => 'category',
    'view_namespace' => 'ttxt-content',
    'menu' => [
        'title' => 'Danh mục',
        'icon' => 'fas fa-folder',
        'route' => 'tenant.trung_tam_xuc_tien_ha_noi.category.index',
        'section' => 'content',
        'sort_order' => 20,
    ],
    'permissions' => ['view', 'create', 'update', 'delete'],
];
