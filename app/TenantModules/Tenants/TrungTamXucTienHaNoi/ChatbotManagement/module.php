<?php

return [
    'name' => 'Cấu hình Chatbot',
    'slug' => 'chatbot_management',
    'view_namespace' => 'ttxt-ai-chatbot',
    'menu' => [
        'title' => 'Cấu hình Chatbot',
        'icon' => 'fas fa-robot',
        'section' => 'systems',
        'sort_order' => 100,
        'items' => [
            'overview' => [
                'title' => 'Tổng quan',
                'route' => 'tenant.trung_tam_xuc_tien_ha_noi.chatbot_management.ai_monitor.overview',
            ],
            'basic' => [
                'title' => 'Cài đặt cơ bản',
                'route' => 'tenant.trung_tam_xuc_tien_ha_noi.chatbot_management.settings.basic',
            ],
            'sync' => [
                'title' => 'Đồng bộ tri thức',
                'route' => 'tenant.trung_tam_xuc_tien_ha_noi.chatbot_management.settings.sync',
            ],
            'knowledge' => [
                'title' => 'Tài liệu nội bộ',
                'route' => 'tenant.trung_tam_xuc_tien_ha_noi.chatbot_management.settings.knowledge',
            ],
            'usage' => [
                'title' => 'Token & chi phí',
                'route' => 'tenant.trung_tam_xuc_tien_ha_noi.chatbot_management.settings.usage',
            ],
            'webhooks' => [
                'title' => 'Webhook nhận vào',
                'route' => 'tenant.trung_tam_xuc_tien_ha_noi.chatbot_management.ai_monitor.webhooks',
            ],
            'prompts' => [
                'title' => 'Kịch bản (Prompts)',
                'route' => 'tenant.trung_tam_xuc_tien_ha_noi.chatbot_management.settings.prompts',
            ],
            'blacklist' => [
                'title' => 'Rào chắn (Blacklist)',
                'route' => 'tenant.trung_tam_xuc_tien_ha_noi.chatbot_management.settings.blacklist',
            ],
            'sessions' => [
                'title' => 'Lịch sử & Insight',
                'route' => 'tenant.trung_tam_xuc_tien_ha_noi.chatbot_management.settings.sessions',
            ],
        ],
    ],
    'permissions' => ['view', 'create', 'update', 'delete'],
];
