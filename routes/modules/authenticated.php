<?php

require __DIR__.'/auth/dashboard_roles_users.php';
require __DIR__.'/auth/leads_campaigns.php';
require __DIR__.'/auth/auto_modules.php';
require __DIR__.'/auth/customers_postventa.php';
require __DIR__.'/auth/calendar_scrum_incidencias.php';
require __DIR__.'/auth/settings_profile_notifications.php';

foreach (glob(__DIR__.'/auth/generated/*.php') ?: [] as $generatedRouteFile) {
    require $generatedRouteFile;
}
