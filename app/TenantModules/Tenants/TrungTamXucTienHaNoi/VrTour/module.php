<?php

return [
    'name' => 'VR Tour',
    'slug' => 'vr-tour',
    'view_namespace' => 'ttxt-vrtour',
    'menu' => [
        'title' => 'VR Tour',
        'icon' => 'fas fa-vr-cardboard',
        'route' => 'legacy.vr-tour.index',
    ],
    'menu_items' => [
        'vr_tour' => [
            'title' => 'VrTour',
            'icon' => 'fas fa-vr-cardboard',
            'sort_order' => 90,
            'items' => [
                'skin' => [
                    'title' => 'Skin',
                    'route' => 'backend_vrtour_skin_index',
                ],
                'hotspot' => [
                    'title' => 'Hotspot',
                    'route' => 'backend_vrtour_hotspot_index',
                ],
                'content' => [
                    'title' => 'Nội dung',
                    'route' => 'backend_vrtour_content_index',
                ],
            ],
        ],
    ],
    'table_route' => 'legacy.vr-tour.table',
    'tables' => ['hotspot', 'panorama'],
    'permissions' => ['view', 'create', 'update', 'delete'],
];
