<?php

return [
    'name' => 'Dashboard',
    'slug' => 'dashboard',
    'view_namespace' => 'ttxt-dashboard',
    'menu' => [
        'title' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'route' => 'backend_dashboard',
        'section' => 'content',
        'sort_order' => 10,
    ],
    'permissions' => ['view'],
];
