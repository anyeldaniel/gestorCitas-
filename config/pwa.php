<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | PWA Enabled
    |--------------------------------------------------------------------------
    */

    'enabled' => (bool) env('PWA_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | PWA Manifest Output Path
    |--------------------------------------------------------------------------
    */

    'manifest_path' => env('PWA_MANIFEST_PATH', 'manifest.json'),

    /*
    |--------------------------------------------------------------------------
    | PWA Service Worker Ignored Paths
    |--------------------------------------------------------------------------
    */

    'ignore_paths' => env('PWA_IGNORE_PATHS', ['/api/', '/livewire/', '/_inertia/']),

    /*
    |--------------------------------------------------------------------------
    | PWA Service Worker Path
    |--------------------------------------------------------------------------
    */

    'sw_path' => env('PWA_SW_PATH', 'null'),

    /*
    |--------------------------------------------------------------------------
    | PWA Manifest
    |--------------------------------------------------------------------------
    */

    'manifest' => [
        'id' => env('PWA_ID', '/'),
        'name' => env('APP_NAME', 'Syncrostyle - The Beauty Room'),
        'short_name' => env('PWA_SHORT_NAME', 'Syncrostyle'),
        'description' => env('PWA_DESCRIPTION', 'Sistema de Gestión Operativa y Citas'),
        'start_url' => env('PWA_START_URL', '/'),
        'scope' => env('PWA_SCOPE', '/'),
        'display_override' => ['standalone', 'minimal-ui'],
        'display' => env('PWA_DISPLAY', 'standalone'),
        'orientation' => env('PWA_ORIENTATION', 'any'),
        'background_color' => env('PWA_BACKGROUND_COLOR', '#ffffff'),
        'theme_color' => env('PWA_THEME_COLOR', '#0A5C9E'),
        'lang' => env('PWA_LANG', 'es'),
        'dir' => env('PWA_DIR', 'ltr'),
        
        'screenshots' => [
            [
                'src' => '/screenshots/desktop.png',
                'sizes' => '1280x720', 
                'type' => 'image/png',
                'form_factor' => 'wide',
                'label' => 'Panel de Terapeutas'
            ],
            [
                'src' => '/screenshots/mobile.png',
                'sizes' => '469x748', 
                'type' => 'image/png',
                'form_factor' => 'narrow',
                'label' => 'Notificaciones'
            ],
        ],
    ], 

    /*
    |--------------------------------------------------------------------------
    | PWA Icons
    |--------------------------------------------------------------------------
    */

    'icons' => [
        [
            'disk' => env('PWA_ICON_DISK', null),
            'path' => env('PWA_ICON_MOBILE_PATH', '/icons/icon-192x192.png'),
            'sizes' => env('PWA_ICON_MOBILE_SIZES', '192x192'),
            'type' => env('PWA_ICON_MOBILE_TYPE', 'image/png'),
        ],
        [
            'disk' => env('PWA_ICON_DISK', null),
            'path' => env('PWA_ICON_DESKTOP_PATH', '/icons/icon-512x512.png'),
            'sizes' => env('PWA_ICON_DESKTOP_SIZES', '512x512'),
            'type' => env('PWA_ICON_DESKTOP_TYPE', 'image/png'),
        ],
    ],

    /**
     * The Apple Touch Icon is used by iOS devices when a user adds the web application to their home screen.
     */
    'apple_touch_icon' => env('PWA_APPLE_TOUCH_ICON', '/icons/icon-192x192.png'), 

    /*
    |--------------------------------------------------------------------------
    | PWA Service Worker Update Interval
    |--------------------------------------------------------------------------
    */

    'update_interval' => env('PWA_UPDATE_INTERVAL', 24),
    'vapid_public_key' => env('VAPID_PUBLIC_KEY', ''),

];