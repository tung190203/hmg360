<?php

return [
    'name' => 'Cẩm nang đầu tư',
    'slug' => 'investment_guide',
    'view_namespace' => 'ttxt-investment',
    'menu' => [
        'title' => 'Cẩm nang đầu tư',
        'icon' => 'fas fa-book',
        'route' => 'backend_investment_guide',
        'section' => 'content',
        'sort_order' => 40,
    ],
    'permissions' => ['view', 'create', 'update', 'delete'],
];
