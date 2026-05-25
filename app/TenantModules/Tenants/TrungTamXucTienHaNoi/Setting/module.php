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
                'route' => 'backend_setting_general',
            ],
            'author' => [
                'title' => 'Tác giả',
                'route' => 'backend_setting_author',
            ],
            'social' => [
                'title' => 'Mạng xã hội',
                'route' => 'backend_setting_social',
            ],
            'seo' => [
                'title' => 'SEO',
                'route' => 'backend_setting_seo',
            ],
        ],
    ],
    'permissions' => ['view', 'update'],
];
