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
    'category' => [
        'label' => 'Danh mục',
        'items' => [
            'add' => 'Thêm',
            'edit' => 'Sửa',
            'delete' => 'Xóa',
        ],
        'super_admin_only' => false,
    ],
    'post' => [
        'label' => 'Bài viết',
        'items' => [
            'add' => 'Thêm',
            'edit' => 'Sửa',
            'delete' => 'Xóa',
            'clone' => 'Nhân bản',
            'import' => 'Import từ URL',
        ],
        'super_admin_only' => false,
    ],
    'investment_guide' => [
        'label' => 'Cẩm nang đầu tư',
        'items' => [
            'add' => 'Thêm',
            'edit' => 'Sửa',
            'delete' => 'Xóa',
            'clone' => 'Nhân bản',
            'import' => 'Import từ URL',
        ],
        'super_admin_only' => false,
    ],
    'popup' => [
        'label' => 'Popup',
        'items' => [
            'add' => 'Thêm',
            'edit' => 'Sửa',
            'delete' => 'Xóa',
        ],
        'super_admin_only' => false,
    ],
    'guest' => [
        'label' => 'Người dùng',
        'items' => [
            'add' => 'Thêm',
            'edit' => 'Sửa',
            'delete' => 'Xóa',
        ],
        'super_admin_only' => false,
    ],
    'menu' => [
        'label' => 'Menu',
        'items' => [
            'add' => 'Thêm',
            'edit' => 'Sửa',
            'delete' => 'Xóa',
        ],
        'super_admin_only' => false,
    ],
    'project' => [
        'label' => 'Dự án',
        'items' => [
            'add' => 'Thêm',
            'edit' => 'Sửa',
            'delete' => 'Xóa',
            'clone' => 'Nhân bản',
        ],
        'super_admin_only' => false,
    ],
    'user' => [
        'label' => 'User',
        'items' => [
            'add' => 'Thêm',
            'edit' => 'Sửa',
            'delete' => 'Xóa',
        ],
        'super_admin_only' => false,
    ],
    'group' => [
        'label' => 'User Group',
        'items' => [
            'add' => 'Thêm',
            'edit' => 'Sửa',
            'delete' => 'Xóa',
        ],
        'super_admin_only' => true,
    ],
    'setting' => [
        'label' => 'Setting',
        'items' => [
            'general' => 'Cài đặt chung',
            'seo' => 'Cài đặt SEO',
            'social' => 'Cài đặt mạng xã hội',
        ],
        'super_admin_only' => true,
    ],
    'chatbot_management' => [
        'label' => 'Cấu hình Chatbot',
        'items' => [
            'basic' => 'Cài đặt Cơ bản',
            'sync' => 'Đồng bộ Trí thức',
            'knowledge' => 'Tài liệu nội bộ',
            'usage' => 'Token & chi phí',
            'webhooks' => 'Webhook nhận vào',
            'prompts' => 'Kịch bản',
            'blacklist' => 'Rào chắn',
            'sessions' => 'Lịch sử & Insight',
        ],
        'super_admin_only' => true,
    ],
];
