<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/6.-Basic-Configuration
    |
    */

    'title' => env('APP_NAME'),
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/6.-Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/6.-Basic-Configuration
    |
    */

    'logo' => '',
    'logo_img' => 'img/PDPLogo_143x296.png',
    'logo_img_class' => 'brand-image elevation-3',
    'logo_img_xl' => 'img/PDPLogo_1143x561.png',
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Performance',

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/6.-Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/7.-Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/7.-Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/7.-Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/7.-Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/7.-Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/6.-Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'dashboard',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Mix
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Mix option for the admin panel.
    |
    | For detailed instructions you can look the laravel mix section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/9.-Other-Configuration
    |
    */

    'enabled_laravel_mix_css' => true,  // Custom
    'enabled_laravel_mix_js' => false,  // Custom
    'laravel_mix_css_path' => 'css/app.css',
    'laravel_mix_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/8.-Menu-Configuration
    |
    */

    'menu' => [
        [
            'role' => 'listitem',
            'text' => 'Home',
            'url'  => 'dashboard',
            'icon' => 'fas fa-fw fa-home',
            'active' => ['dashboard/*'],
            'can' => ['dashboard'],
            'hiddenInViewAs' => true
        ],
        [
            'text' => 'My Goals',
            'role' => 'listitem',
            'url'  => 'goal/current',
            'icon' => 'fas fa-fw fa-bullseye',
            'active' => ['goal/*'],
            'can' => ['goals']
        ],
        [
            'role' => 'listitem',
            'text' => 'My Conversations',
            'url'  => 'conversation/templates',
            'icon' => 'fas fa-fw fa-bullseye',
            'active' => ['conversation/*'],
            'can' => ['conversions']
        ],
        [
            'role' => 'listitem',
            'text' => 'My Team',
            'id' => 'my-team-menu',
            /* 'url'  => '#', */
            'icon' => 'fas fa-fw fa-users',
            'active' => ['my-team/*'],
            'can' => ['my team'],
            'submenu' => [
                /* [
                    'text' => 'Team Goals',
                    'url'  => 'my-team/team-goals/share-my-goals',
                    'active' => ['my-team/team-goals/*']
                ] *//* ,
                [
                    'text' => 'Team Conversations',
                    'url'  => 'my-team/conversations',
                    'active' => ['my-team/conversations/*'],
                ], */
                [
                    'text' => 'Team Members',
                    'url'  => 'my-team/my-employees',
                    'active' => ['my-team/my-employees/*'],
                ],
                // [
                //     'text' => 'Statistic and Reports',
                //     'url'  => 'my-team/statistics/sharedsummary',
                //     'active' => ['my-team/statistics/*']
                // ],
                // [
                //     'text' => 'Notify Team Members',
                //     'url'  => 'my-team/notify-team-members',
                //     'active' => ['my-team/members/*'],
                // ]
            ]
        ],
        [
            'role' => 'listitem',
            'text' => 'HR Administration',
            'id' => 'hr-admin-menu',
            'url'  => '#',
            'icon' => 'fas fa-fw fa-cog',
            'active' => ['hradmin/*'],
            'can' => ['hr admin'],
            'submenu' => [
                [
                    'text' => 'My Organization',
                    'url'  => 'hradmin/myorg',
                    'active' => ['hradmin/myorg/*']
                ],
                [
                    'text' => 'Share Employees',
                    'url'  => 'hradmin/employeeshares',
                    'active' => ['hradmin/employeeshares/*']
                ],
                [
                    'text' => 'Excuse Employees',
                    'url'  => 'hradmin/excuseemployees',
                    'active' => ['hradmin/excuseemployees/*']
                ],
                [
                    'text' => 'Goal Bank',
                    'url'  => 'hradmin/goalbank',
                    'active' => ['hradmin/goalbank/*']
                ],
                // [
                //     'text' => 'Notifications',
                //     'url'  => 'hradmin/notifications',
                //     'active' => ['hradmin/notifications/*']
                // ],
                [
                    'text' => 'Statistic and Reports',
                    'url'  => 'hradmin/statistics/sharedsummary',
                    'active' => ['hradmin/statistics/*']
                ]

            ],
        ],
        [
            'role' => 'listitem',
            'text' => 'System Administration',
            'id' => 'sys-admin-menu',
            'url'  => '#',
            'icon' => 'fas fa-fw fa-cog',
            'active' => ['sysadmin/*'],
            'can' => ['sys admin'],
            'submenu' => [
                [
                    'text' => 'Employee List',
                    'url'  => 'sysadmin/employeelists',
                    'active' => ['sysadmin/employeelists/*']
                ],
                [
                    'text' => 'Share Employees',
                    'url'  => 'sysadmin/employeeshares',
                    'active' => ['sysadmin/employeeshares/*']
                ],
                [
                    'text' => 'Excuse Employees',
                    'url'  => 'sysadmin/excuseemployees',
                    'active' => ['sysadmin/excuseemployees/*']
                ],
                [
                    'text' => 'Goal Bank',
                    'url'  => 'sysadmin/goalbank',
                    'active' => ['sysadmin/goalbank/*']
                ],
                [
                    'text' => 'Supervisor Overrides',
                    'url'  => 'sysadmin/supervisoroverrides',
                    'active' => ['sysadmin/supervisoroverrides/*']
                ],
                [
                    'text' => 'Tags Management',
                    'url'  => 'sysadmin/tags',
                    'active' => ['sysadmin/tags/*']
                ],
                [
                    'text' => 'Unlock Conversations',
                    'url'  => 'sysadmin/unlock/unlockconversation',
                    'active' => ['sysadmin/unlock/*']
                ],
                [
                    'text' => 'Notifications',
                    'url'  => 'sysadmin/notifications',
                    'active' => ['sysadmin/notifications/*']
                ],
                [
                    'text' => 'Resource Manage',
                    'url'  => 'sysadmin/resource-manage',
                    'active' => ['sysadmin/resource-manage/*']
                ],
                [
                    'text' => 'Access and Permissions',
                    'url'  => 'sysadmin/accesspermissions',
                    'active' => ['sysadmin/accesspermissions/*']
                ],
                [
                    'text' => 'Switch Identity',
                    'url'  => 'sysadmin/switch-identity',
                    'active' => ['sysadmin/switch-identity/*']
                ],
                [
                    'text' => 'Statistic and Reports',
                    'url'  => 'sysadmin/statistics/sharedsummary',
                    'active' => ['sysadmin/statistics/*']
                ],
                [
                    'text' => 'System Security',
                    'url'  => 'sysadmin/system-security/access-logs',
                    'active' => ['sysadmin/system-security/*']
                ],
                [
                    'text' => 'Schedule Job Audit',
                    'url'  => 'sysadmin/job-schedule-audit',
                    'active' => ['sysadmin/system-security/*']
                ],
                [
                    'text' => 'Auditing',
                    'url'  => 'sysadmin/auditing',
                    'active' => ['sysadmin/auditing/*']
                ],
                [
                    'text' => 'Welcome Message',
                    'url'  => 'sysadmin/messageeditor',
                    'active' => ['sysadmin/messageeditor/*']
                ],
            ],
        ],
        [
            'role' => 'listitem',
            'text' => 'Service Representative',
            'id' => 'sr-menu',
            'url'  => '#',
            'icon' => 'fas fa-fw fa-cog',
            'active' => ['sysadmin/*'],
            'can' => ['service representative'],
            'submenu' => [
                [
                    'text' => 'Employee List',
                    'url'  => 'sysadmin/employeelists',
                    'active' => ['sysadmin/employeelists/*']
                ],
                [
                    'text' => 'Switch Identity',
                    'url'  => 'sysadmin/switch-identity',
                    'active' => ['sysadmin/switch-identity/*']
                ],
            ],
        ],
        [
            'role' => 'listitem',
            'text' => 'Resources',
            // 'url'  => 'resource/user-guide',
            'url'  => 'resources',
            'icon' => 'fas fa-fw fa-book',
            // 'active' => ['resource/*']
            'active' => ['resources/*']
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/8.-Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/9.-Other-Configuration
    |
    */

    'plugins' => [
        'FontAwesome' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/fontawesome-free/css/all.min.css'
                ]
            ]
        ],
        'Fonts' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic'
                ]
            ]
        ],

        'Datatables' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/9.-Other-Configuration
    */

    'livewire' => false,
];
