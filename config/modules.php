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
            'component' => 'inbox/InvoiceDispatchInbox.vue',
            'menu_permission' => 'menu.inbox',
            'view_permission' => 'menu.inbox',
            'auto_menu' => false,
            'enabled' => true,
        ],
        [
            'key' => 'invoice-dispatch-preview',
            'label' => 'Preview bandeja de envios',
            'subtitle' => 'Mockup no funcional del rediseño de bandeja para revision visual',
            'path' => '/inbox/facturas-preview',
            'component' => 'inbox/InvoiceDispatchInboxPreview.vue',
            'menu_permission' => 'menu.inbox',
            'view_permission' => 'menu.inbox',
            'auto_menu' => false,
            'enabled' => true,
        ],
        [
            'key' => 'inbox-campaigns',
            'label' => 'Campañas',
            'subtitle' => 'Recordatorios y promociones con un flujo claro de segmentacion, mensaje y programacion',
            'path' => '/inbox/campanas',
            'component' => 'inbox/InboxCampaignsView.vue',
            'menu_permission' => 'menu.inbox',
            'view_permission' => 'menu.inbox',
            'auto_menu' => false,
            'enabled' => true,
        ],
    ],
];
