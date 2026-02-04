<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Lead\LeadController;
use App\Http\Controllers\Lead\LeadDataController;
use App\Http\Controllers\Lead\LeadImportController;
use App\Http\Controllers\Lead\LostLeadController;
use App\Http\Controllers\Lead\WaitingLeadController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Calendar\CalendarEventController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Incidence\IncidenceController;
use App\Http\Controllers\PostVenta\ContadorController;
use App\Http\Controllers\PostVenta\CertificadoController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\RelatedLookupController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Campaign\WhatsAppCampaignController;
use App\Http\Controllers\Campaign\EmailCampaignController;
use App\Http\Controllers\EmailUnsubscribeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Public unsubscribe endpoint for email campaigns
Route::get('/email/unsubscribe', [EmailUnsubscribeController::class, 'unsubscribe'])->name('email.unsubscribe');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard/summary', [DashboardController::class, 'summary'])->name('dashboard.summary');

    Route::middleware('permission:roles.view')->group(function () {
        Route::get('/roles', function () {
            return view('dashboard');
        })->name('roles.index');

        Route::get('/roles/data', [RoleController::class, 'data'])->name('roles.data');
        Route::get('/roles/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
    });

    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('permission:roles.create')
        ->name('roles.store');

    Route::put('/roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles.update')
        ->name('roles.update');

    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:roles.delete')
        ->name('roles.destroy');

    Route::middleware('permission:users.view')->group(function () {
        Route::get('/users', function () {
            return view('dashboard');
        })->name('users.index');

        Route::get('/users/data', [UserController::class, 'index'])->name('users.data');

        Route::get('/users/role-options', [UserController::class, 'roleOptions'])->name('users.roleOptions');
    });

    Route::middleware('permission:leads.view')->group(function () {
        Route::get('/leads', function () {
            return view('dashboard');
        })->name('leads.index');

        Route::get('/leads/list', function () {
            return view('dashboard');
        })->name('leads.list');

        // Refactored: Data queries moved to LeadDataController
        Route::get('/leads/data', [LeadDataController::class, 'tableData'])->name('leads.data');
        Route::get('/leads/board-data', [LeadDataController::class, 'boardData'])->name('leads.boardData');

        Route::get('/leads/whatsapp', function () {
            return view('dashboard');
        })->name('leads.whatsapp');

        Route::get('/leads/email', function () {
            return view('dashboard');
        })->name('leads.email');

        Route::get('/leads/whatsapp/recipients', [WhatsAppCampaignController::class, 'recipients'])
            ->name('leads.whatsapp.recipients');

        Route::get('/leads/email/recipients', [EmailCampaignController::class, 'recipients'])
            ->name('leads.email.recipients');

        Route::get('/leads/whatsapp-campaigns', [WhatsAppCampaignController::class, 'index'])
            ->name('leads.whatsapp.campaigns.index');

        Route::get('/leads/email-campaigns', [EmailCampaignController::class, 'index'])
            ->name('leads.email.campaigns.index');

        Route::get('/leads/whatsapp-campaigns/{campaign}', [WhatsAppCampaignController::class, 'show'])
            ->whereNumber('campaign')
            ->name('leads.whatsapp.campaigns.show');

        Route::get('/leads/email-campaigns/{campaign}', [EmailCampaignController::class, 'show'])
            ->whereNumber('campaign')
            ->name('leads.email.campaigns.show');
        
            Route::get('/desistidos', function () { return view('dashboard'); })->name('desistidos.index');
            Route::get('/desistidos/data', [LostLeadController::class, 'index'])->name('desistidos.data');
            Route::get('/desistidos/{lostLead}', [LostLeadController::class, 'show'])
                ->whereNumber('lostLead')
                ->name('desistidos.show');

            Route::get('/espera', function () { return view('dashboard'); })->name('espera.index');
            Route::get('/espera/data', [WaitingLeadController::class, 'index'])->name('espera.data');
            Route::get('/espera/{waitingLead}', [WaitingLeadController::class, 'show'])
                ->whereNumber('waitingLead')
                ->name('espera.show');
    });

    Route::patch('/desistidos/{lostLead}', [LostLeadController::class, 'update'])
        ->whereNumber('lostLead')
        ->middleware('permission:leads.update')
        ->name('desistidos.update');

    Route::patch('/espera/{waitingLead}', [WaitingLeadController::class, 'update'])
        ->whereNumber('waitingLead')
        ->middleware('permission:leads.update')
        ->name('espera.update');

    Route::post('/espera/{waitingLead}/reactivate', [WaitingLeadController::class, 'reactivate'])
        ->whereNumber('waitingLead')
        ->middleware('permission:leads.create')
        ->name('espera.reactivate');

    Route::middleware('permission:customers.view')->group(function () {
        Route::get('/customers', function () {
            return view('dashboard');
        })->name('customers.index');

        Route::get('/customers/data', [CustomerController::class, 'data'])->name('customers.data');
    });

    Route::post('/customers', [CustomerController::class, 'store'])
        ->middleware('permission:customers.create')
        ->name('customers.store');

    Route::post('/customers/import', [CustomerController::class, 'importCsv'])
        ->middleware('permission:customers.create')
        ->name('customers.import');

    Route::middleware('permission:calendar.view')->group(function () {
        Route::get('/calendar', function () {
            return view('dashboard');
        })->name('calendar.index');

        Route::get('/calendar/events', [CalendarEventController::class, 'index'])->name('calendar.events');

        Route::get('/related-lookup', [RelatedLookupController::class, 'index'])->name('related.lookup');
    });

    Route::middleware('permission:incidencias.view')->group(function () {
        Route::get('/incidencias', function () {
            return view('dashboard');
        })->name('incidencias.index');

        Route::get('/backlog', function () {
            return view('dashboard');
        })->name('backlog.index');

        Route::get('/incidencias/data', [IncidenceController::class, 'tableData'])->name('incidencias.data');

        Route::get('/backlog/board-data', [IncidenceController::class, 'boardData'])->name('backlog.boardData');
    });

    Route::post('/incidencias/import', [IncidenceController::class, 'importCsv'])
        ->middleware('permission:incidencias.create')
        ->name('incidencias.import');

    Route::middleware('permission:customers.view')->group(function () {
        Route::get('/postventa/clientes', function () {
            return view('dashboard');
        })->name('postventa.customers');
    });

    Route::middleware('permission:contadores.view')->group(function () {
        Route::get('/postventa/contadores', function () {
            return view('dashboard');
        })->name('postventa.contadores');

        Route::get('/postventa/contadores/data', [ContadorController::class, 'data'])->name('postventa.contadores.data');
    });

    Route::post('/postventa/contadores', [ContadorController::class, 'store'])
        ->middleware('permission:contadores.create')
        ->name('postventa.contadores.store');

    Route::post('/postventa/contadores/import', [ContadorController::class, 'importCsv'])
        ->middleware('permission:contadores.create')
        ->name('postventa.contadores.import');

    Route::put('/postventa/contadores/{contador}', [ContadorController::class, 'update'])
        ->middleware('permission:contadores.update')
        ->name('postventa.contadores.update');

    Route::delete('/postventa/contadores/{contador}', [ContadorController::class, 'destroy'])
        ->middleware('permission:contadores.delete')
        ->name('postventa.contadores.destroy');

    Route::middleware('permission:certificados.view')->group(function () {
        Route::get('/postventa/certificados', function () {
            return view('dashboard');
        })->name('postventa.certificados');

        Route::get('/postventa/certificados/data', [CertificadoController::class, 'data'])->name('postventa.certificados.data');
    });

    Route::post('/postventa/certificados', [CertificadoController::class, 'store'])
        ->middleware('permission:certificados.create')
        ->name('postventa.certificados.store');

    Route::post('/postventa/certificados/import-data', [CertificadoController::class, 'importData'])
        ->middleware('permission:certificados.create')
        ->name('postventa.certificados.importData');

    Route::post('/postventa/certificados/import-images', [CertificadoController::class, 'importImages'])
        ->middleware('permission:certificados.create')
        ->name('postventa.certificados.importImages');

    Route::put('/postventa/certificados/{certificado}', [CertificadoController::class, 'update'])
        ->middleware('permission:certificados.update')
        ->name('postventa.certificados.update');

    Route::delete('/postventa/certificados/{certificado}', [CertificadoController::class, 'destroy'])
        ->middleware('permission:certificados.delete')
        ->name('postventa.certificados.destroy');

    Route::post('/calendar/events', [CalendarEventController::class, 'store'])
        ->middleware('permission:calendar.create')
        ->name('calendar.events.store');

    Route::put('/calendar/events/{event}', [CalendarEventController::class, 'update'])
        ->middleware('permission:calendar.update')
        ->name('calendar.events.update');

    Route::delete('/calendar/events/{event}', [CalendarEventController::class, 'destroy'])
        ->middleware('permission:calendar.delete')
        ->name('calendar.events.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::put('/customers/{customer}', [CustomerController::class, 'update'])
        ->middleware('permission:customers.update')
        ->name('customers.update');

    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])
        ->middleware('permission:customers.delete')
        ->name('customers.destroy');

    Route::post('/leads', [LeadController::class, 'store'])
        ->middleware(['permission:leads.create', 'check.lead.permissions:leads.create'])
        ->name('leads.store');

    Route::put('/leads/{lead}', [LeadController::class, 'update'])
        ->middleware(['permission:leads.update', 'check.lead.permissions:leads.update'])
        ->name('leads.update');

    Route::post('/leads/whatsapp-campaigns', [WhatsAppCampaignController::class, 'store'])
        ->middleware(['permission:leads.update', 'check.campaign.permissions:campaigns.create'])
        ->name('leads.whatsapp.campaigns.store');

    Route::post('/leads/email-campaigns', [EmailCampaignController::class, 'store'])
        ->middleware(['permission:leads.update', 'check.campaign.permissions:campaigns.create'])
        ->name('leads.email.campaigns.store');

    Route::post('/leads/email-campaigns/{campaign}/send', [EmailCampaignController::class, 'send'])
        ->whereNumber('campaign')
        ->middleware(['permission:leads.update', 'check.campaign.permissions:campaigns.send'])
        ->name('leads.email.campaigns.send');

    Route::patch('/leads/whatsapp-campaign-recipients/{recipient}', [WhatsAppCampaignController::class, 'updateRecipient'])
        ->whereNumber('recipient')
        ->middleware('permission:leads.update')
        ->name('leads.whatsapp.recipients.update');

    // Refactored: Import moved to LeadImportController
    Route::post('/leads/import/prospectos', [LeadImportController::class, 'import'])
        ->middleware('permission:leads.create')
        ->name('leads.import.prospectos');

    Route::post('/desistidos/import', [LostLeadController::class, 'importCsv'])
        ->middleware('permission:leads.create')
        ->name('desistidos.import');

    Route::post('/espera/import', [WaitingLeadController::class, 'importCsv'])
        ->middleware('permission:leads.create')
        ->name('espera.import');

    Route::post('/incidencias', [IncidenceController::class, 'store'])
        ->middleware('permission:incidencias.create')
        ->name('incidencias.store');

    Route::put('/incidencias/{incidence}', [IncidenceController::class, 'update'])
        ->middleware('permission:incidencias.update')
        ->name('incidencias.update');

    Route::delete('/incidencias/{incidence}', [IncidenceController::class, 'destroy'])
        ->middleware('permission:incidencias.delete')
        ->name('incidencias.destroy');

    Route::patch('/leads/{lead}/move-stage', [LeadController::class, 'moveStage'])
        ->middleware('permission:leads.update')
        ->name('leads.moveStage');

    // Refactored: Reorder moved to LeadDataController
    Route::patch('/leads/reorder', [LeadDataController::class, 'reorder'])
        ->middleware('permission:leads.update')
        ->name('leads.reorder');

    Route::patch('/incidencias/{incidence}/move-stage', [IncidenceController::class, 'moveStage'])
        ->middleware('permission:incidencias.update')
        ->name('incidencias.moveStage');

    Route::patch('/incidencias/reorder', [IncidenceController::class, 'reorder'])
        ->middleware('permission:incidencias.update')
        ->name('incidencias.reorder');

    Route::patch('/leads/{lead}/archive', [LeadController::class, 'archive'])
        ->middleware('permission:leads.update')
        ->name('leads.archive');

    Route::post('/leads/{lead}/desist', [LostLeadController::class, 'createFromLead'])
        ->middleware('permission:leads.update')
        ->name('leads.desist');

    Route::post('/leads/{lead}/wait', [WaitingLeadController::class, 'createFromLead'])
        ->middleware('permission:leads.update')
        ->name('leads.wait');

    Route::patch('/incidencias/{incidence}/archive', [IncidenceController::class, 'archive'])
        ->middleware('permission:incidencias.update')
        ->name('incidencias.archive');

    Route::post('/users', [UserController::class, 'store'])
        ->middleware('permission:users.create')
        ->name('users.store');

    Route::put('/users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.update')
        ->name('users.update');

    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:users.delete')
        ->name('users.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Configuracion - UI SPA (mismo wrapper que el resto de modulos)
    Route::get('/configuracion', function () {
        return view('dashboard');
    })->name('settings.index');

    Route::post('/configuracion/logo', [SettingsController::class, 'uploadLogo'])
        ->name('settings.logo.upload');

    Route::get('/configuracion/logo-path', [SettingsController::class, 'logoPath'])
        ->name('settings.logo.path');

    Route::post('/configuracion/logo-mark', [SettingsController::class, 'uploadLogoMark'])
        ->name('settings.logo.mark.upload');

    Route::post('/configuracion/logo-full', [SettingsController::class, 'uploadLogoFull'])
        ->name('settings.logo.full.upload');
    Route::post('/api/chatbot/query', [\App\Http\Controllers\ChatbotController::class, 'query']);

    // Simple UI for user help chat
    Route::get('/chat', function () {
        return view('chat');
    })->middleware('auth')->name('chat');

    // Embeddable fragment used by floating widget
    Route::get('/chat/widget', function () {
        return view('chat-embed');
    })->middleware('auth')->name('chat.widget');

    Route::get('/configuracion/logo-paths', [SettingsController::class, 'logoPaths'])
        ->name('settings.logo.paths');
});

require __DIR__.'/modules/demos.php';

require __DIR__.'/auth.php';
