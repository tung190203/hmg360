<?php

return [
    'name' => 'Cài đặt hệ thống',
    'slug' => 'setting',
    'view_namespace' => 'ttxt-website',
    'menu' => [
        'title' => 'Cài đặt hệ thống',
        'icon' => 'fas fa-cog',
        'section' => 'systems',
        'sort_order' => 110,
        'items' => [
            'general' => [
                'title' => 'Cấu hình chung',
                'route' => 'tenant.trung_tam_xuc_tien_ha_noi.setting.general',
            ],
            'author' => [
                'title' => 'Tác giả',
                'route' => 'tenant.trung_tam_xuc_tien_ha_noi.setting.author',
            ],
            'social' => [
                'title' => 'Mạng xã hội',
                'route' => 'tenant.trung_tam_xuc_tien_ha_noi.setting.social',
            ],
            'seo' => [
                'title' => 'SEO',
                'route' => 'tenant.trung_tam_xuc_tien_ha_noi.setting.seo',
            ],
        ],
    ],
    'permissions' => ['view', 'update'],
];
