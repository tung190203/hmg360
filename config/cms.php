<?php

return [
    'prefix_admin' => env('PREFIX_ADMIN', 'backend'),
    'logo' => [
        'lg' => '<b>HMG360</b>',
        'mini' => '<b>HMG</b>',
    ],
    'name' => 'HMG360',
    'version' => '1.0-core',
    'backend_module' => [
        'platform' => [
            'title' => 'Platform',
            'items' => [
                'dashboard' => [
                    'icon' => 'fas fa-tachometer-alt',
                    'route' => 'backend_dashboard',
                    'title' => 'Dashboard',
                ],
                'tenants' => [
                    'icon' => 'fas fa-building',
                    'route' => 'backend_core_tenants',
                    'title' => 'Tenants',
                ],
                'modules' => [
                    'icon' => 'fas fa-cubes',
                    'route' => 'backend_core_modules',
                    'title' => 'Modules',
                ],
            ]
        ],
        'systems' => [
            'title' => 'Systems',
            'items' => [
                'file_manager' => [
                    'icon' => 'fas fa-file-archive',
                    'route' => 'backend_file_manager',
                    'title' => 'Files',
                ],
            ]
        ]
    ]
];
