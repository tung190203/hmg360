<?php

return [
    'name' => 'Tin tức',
    'slug' => 'post',
    'view_namespace' => 'ttxt-content',
    'menu' => [
        'title' => 'Tin tức',
        'icon' => 'fas fa-newspaper',
        'route' => 'tenant.trung_tam_xuc_tien_ha_noi.post.index',
        'section' => 'content',
        'sort_order' => 30,
    ],
    'permissions' => ['view', 'create', 'update', 'delete'],
];
