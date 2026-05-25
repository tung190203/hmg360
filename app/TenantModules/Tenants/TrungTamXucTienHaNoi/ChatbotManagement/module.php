<?php

return [
    'name' => 'Cấu hình Chatbot',
    'slug' => 'chatbot_management',
    'view_namespace' => 'ttxt-ai-chatbot',
    'menu' => [
        'title' => 'Cấu hình Chatbot',
        'icon' => 'fas fa-robot',
        'route' => 'backend_setting_chatbot',
        'section' => 'systems',
        'sort_order' => 100,
    ],
    'permissions' => ['view', 'create', 'update', 'delete'],
];
