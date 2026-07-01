<?php

return [
    // Canonical menu feed — source of truth is genz-admin (genz-admin-apis).
    // The RMS is now a read-only consumer: `php artisan menu:sync` pulls this
    // feed into the local categories + menu_items mirror so recipes/costing keep
    // referencing menu_items by id.
    'admin_menu_url' => env('ADMIN_MENU_URL', 'http://localhost:8002/api/public/menu'),
];
