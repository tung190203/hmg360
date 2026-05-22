<?php

return [
    'backend_access' => [
        'label' => 'Truy cập backend',
        'super_admin_only' => false,
    ],
    'dashboard' => [
        'label' => 'Dashboard',
        'super_admin_only' => false,
    ],
    'tenants' => [
        'label' => 'Quản lý tenant',
        'items' => [
            'view' => 'Xem',
            'create' => 'Thêm',
            'update' => 'Sửa',
        ],
        'super_admin_only' => true,
    ],
    'modules' => [
        'label' => 'Quản lý module',
        'items' => [
            'view' => 'Xem',
            'create' => 'Thêm',
            'update' => 'Sửa',
            'toggle' => 'Bật/tắt',
        ],
        'super_admin_only' => true,
    ],
    'roles' => [
        'label' => 'Role & permission',
        'items' => [
            'view' => 'Xem',
            'create' => 'Thêm',
            'update' => 'Sửa',
        ],
        'super_admin_only' => true,
    ],
    'file_manager' => [
        'label' => 'Quản lý file',
        'super_admin_only' => true,
    ],
];
