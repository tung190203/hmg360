<?php

return [
    'name' => 'VR Tour',
    'slug' => 'vr-tour',
    'view_namespace' => 'ttxt-vrtour',
    'menu' => [
        'title' => 'VR Tour',
        'icon' => 'fas fa-vr-cardboard',
        'route' => 'tenant.trung_tam_xuc_tien_ha_noi.vr_tour.index',
        'section' => 'content',
        'sort_order' => 90,
    ],
    'menu_items' => [
        'vr_tour' => [
            'title' => 'VrTour',
            'icon' => 'fas fa-vr-cardboard',
            'section' => 'content',
            'sort_order' => 90,
            'items' => [
                'skin' => [
                    'title' => 'Skin',
                    'route' => 'tenant.trung_tam_xuc_tien_ha_noi.vr_tour.skin.index',
                ],
                'hotspot' => [
                    'title' => 'Hotspot',
                    'route' => 'tenant.trung_tam_xuc_tien_ha_noi.vr_tour.hotspot.index',
                ],
                'content' => [
                    'title' => 'Nội dung',
                    'route' => 'tenant.trung_tam_xuc_tien_ha_noi.vr_tour.content.index',
                ],
            ],
        ],
    ],
    'table_route' => 'tenant.trung_tam_xuc_tien_ha_noi.vr_tour.index',
    'tables' => ['hotspot', 'panorama'],
    'permissions' => ['view', 'create', 'update', 'delete'],
];
