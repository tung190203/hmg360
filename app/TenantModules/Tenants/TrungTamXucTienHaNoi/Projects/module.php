<?php

return [
    'name' => 'Projects',
    'slug' => 'projects',
    'view_namespace' => 'legacy-projects',
    'menu' => [
        'title' => 'Dự án',
        'icon' => 'fas fa-project-diagram',
        'route' => 'backend_project',
        'section' => 'content',
        'sort_order' => 50,
    ],
    'menu_items' => [
        'projects' => [
            'title' => 'Dự án',
            'icon' => 'fas fa-project-diagram',
            'route' => 'backend_project',
            'section' => 'content',
            'sort_order' => 50,
        ],
    ],
    'permissions' => ['view', 'create', 'update', 'delete'],
];
