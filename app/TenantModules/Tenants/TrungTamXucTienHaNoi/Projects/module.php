<?php

return [
    'name' => 'Projects',
    'slug' => 'projects',
    'view_namespace' => 'legacy-projects',
    'menu' => [
        'title' => 'Dự án',
        'icon' => 'fas fa-project-diagram',
        'route' => 'backend_project',
    ],
    'menu_items' => [
        'projects' => [
            'title' => 'Dự án',
            'icon' => 'fas fa-project-diagram',
            'route' => 'backend_project',
            'sort_order' => 60,
        ],
    ],
    'permissions' => ['view', 'create', 'update', 'delete'],
];
