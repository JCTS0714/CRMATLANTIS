<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dynamic Modules Registry
    |--------------------------------------------------------------------------
    |
    | Single source of truth for modules that should be auto-registered in:
    | - main dashboard route mounting
    | - sidebar menu rendering
    | - app view host resolution
    |
    */
    'dynamic' => [
        [
            'key' => 'invoice-dispatch',
            'label' => 'Bandeja de envios',
            'subtitle' => 'Envio de facturas por WhatsApp API, fallback manual y email',
            'path' => '/inbox/facturas',
            'component' => 'InvoiceDispatchInbox.vue',
            'menu_permission' => 'menu.inbox',
            'view_permission' => 'menu.inbox',
            'auto_menu' => false,
            'enabled' => true,
        ],
    ],
];
