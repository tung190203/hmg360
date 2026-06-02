<?php

return [
    'name' => 'Popup',
    'slug' => 'popup',
    'view_namespace' => 'ttxt-content',
    'menu' => [
        'title' => 'Popup',
        'icon' => 'fas fa-window-restore',
        'route' => 'tenant.trung_tam_xuc_tien_ha_noi.popup.index',
        'section' => 'content',
        'sort_order' => 70,
    ],
    'permissions' => ['view', 'create', 'update', 'delete'],
];
