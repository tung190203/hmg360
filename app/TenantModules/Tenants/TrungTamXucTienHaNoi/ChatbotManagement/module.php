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
                'route' => 'backend_chatbot_overview',
            ],
            'basic' => [
                'title' => 'Cài đặt cơ bản',
                'route' => 'backend_chatbot_basic',
            ],
            'sync' => [
                'title' => 'Đồng bộ tri thức',
                'route' => 'backend_chatbot_sync',
            ],
            'knowledge' => [
                'title' => 'Tài liệu nội bộ',
                'route' => 'backend_chatbot_knowledge',
            ],
            'usage' => [
                'title' => 'Token & chi phí',
                'route' => 'backend_chatbot_usage',
            ],
            'webhooks' => [
                'title' => 'Webhook nhận vào',
                'route' => 'backend_chatbot_webhooks',
            ],
            'prompts' => [
                'title' => 'Kịch bản (Prompts)',
                'route' => 'backend_chatbot_prompts',
            ],
            'blacklist' => [
                'title' => 'Rào chắn (Blacklist)',
                'route' => 'backend_chatbot_blacklist',
            ],
            'sessions' => [
                'title' => 'Lịch sử & Insight',
                'route' => 'backend_chatbot_sessions',
            ],
        ],
    ],
    'permissions' => ['view', 'create', 'update', 'delete'],
];
