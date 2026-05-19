<?php

return [
    'prefix_admin' => env('PREFIX_ADMIN', 'backend'),
    'logo' => [
        'lg' => '<b>HMG360</b>',
        'mini' => '<b>HMG</b>',
    ],
    'name' => 'HMG360',
    'version' => '4.0',
    'backend_module' => [
        'contents' => [
            'title' => 'Content',
            'items' => [
                'dashboard' => [
                    'icon' => 'fas fa-tachometer-alt',
                    'route' => 'backend_dashboard',
                    'title' => 'Dashboard',
                ],
                'category' => [
                    'icon' => 'fas fa-th',
                    'route' => 'backend_category',
                    'title' => 'Danh mục',
                ],
                'post' => [
                    'icon' => 'far fa-newspaper',
                    'route' => 'backend_post',
                    'title' => 'Tin tức',
                ],
                'investment_guide' => [
                    'icon' => 'fas fa-book',
                    'route' => 'backend_investment_guide',
                    'title' => 'Cẩm nang đầu tư',
                ],
                'menu' => [
                    'icon' => 'fas fa-bars',
                    'route' => 'backend_menu',
                    'title' => 'Navigation',
                ],
                'project' => [
                    'icon' => 'fas fa-project-diagram',
                    'route' => 'backend_project',
                    'title' => 'Dự án',
                ],
                'popup' => [
                    'icon' => 'fas fa-window-restore',
                    'route' => 'backend_popup',
                    'title' => 'Popup',
                ],
                'guest' => [
                    'icon' => 'fas fa-user',
                    'route' => 'backend_guest',
                    'title' => 'Người dùng',
                ],
               'vr_tour' => [
                   'icon' => 'fas fa-vr-cardboard',
                   'title' => 'VrTour',
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
                       ]
                   ]
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
                'user' => [
                    'icon' => 'fas fa-users',
                    'title' => 'Quản lý user',
                    'items' => [
                        'user' => [
                            'title' => 'User',
                            'route' => 'backend_user'
                        ],
                        'group' => [
                            'title' => 'Group',
                            'route' => 'backend_group'
                        ]
                    ]
                ],
                'setting' => [
                    'icon' => 'fas fa-cogs',
                    'title' => 'Cài đặt hệ thống',
                    'items' => [
                        'general' => [
                            'title' => 'Cài đặt chung',
                            'route' => 'backend_setting_general'
                        ],
                        'seo' => [
                            'title' => 'SEO',
                            'route' => 'backend_setting_seo'
                        ],
                        'social' => [
                            'title' => 'Mạng xã hội',
                            'route' => 'backend_setting_social'
                        ]
                    ]
                ],
                'chatbot_management' => [
                    'icon' => 'fas fa-robot',
                    'title' => 'Cấu hình Chatbot',
                    'items' => [
                        'overview' => [
                            'title' => 'Tổng quan',
                            'route' => 'backend_chatbot_overview'
                        ],
                        'basic' => [
                            'title' => 'Cài đặt cơ bản',
                            'route' => 'backend_chatbot_basic'
                        ],
                        'sync' => [
                            'title' => 'Đồng bộ trí thức',
                            'route' => 'backend_chatbot_sync'
                        ],
                        'knowledge' => [
                            'title' => 'Tài liệu nội bộ',
                            'route' => 'backend_chatbot_knowledge'
                        ],
                        'usage' => [
                            'title' => 'Token & chi phí',
                            'route' => 'backend_chatbot_usage'
                        ],
                        'webhooks' => [
                            'title' => 'Webhook nhận vào',
                            'route' => 'backend_chatbot_webhooks'
                        ],
                        'prompts' => [
                            'title' => 'Kịch bản (Prompts)',
                            'route' => 'backend_chatbot_prompts'
                        ],
                        'blacklist' => [
                            'title' => 'Rào chắn (Blacklist)',
                            'route' => 'backend_chatbot_blacklist'
                        ],
                        'sessions' => [
                            'title' => 'Lịch sử & Insight',
                            'route' => 'backend_chatbot_sessions'
                        ]
                    ]
                ],
            ]
        ]
    ]
];
