<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\ConsultantController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\DiagnosticController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

// Public & Client Diagnostic Flow (diagnostics.bridge-tailor.com)
Route::get('/', [DiagnosticController::class, 'landing'])->name('diagnostic.landing');
Route::get('/assess', [DiagnosticController::class, 'wizard'])->name('diagnostic.wizard');
Route::post('/assess', [DiagnosticController::class, 'submit'])->name('diagnostic.submit');

Route::get('/report/{ulid}', [DiagnosticController::class, 'showReport'])->name('diagnostic.report');
Route::get('/report/{ulid}/print', [DiagnosticController::class, 'printReport'])->name('diagnostic.print');
Route::post('/report/{ulid}/consultation', [DiagnosticController::class, 'requestConsultation'])->name('diagnostic.consultation');
Route::post('/report/{ulid}/documents', [DiagnosticController::class, 'uploadDocument'])->name('diagnostic.document.upload');
Route::post('/report/{ulid}/team-invites', [DiagnosticController::class, 'createTeamInvite'])->name('diagnostic.team.create');

// Team respondent contribution link
Route::get('/team/{token}', [DiagnosticController::class, 'showTeamInvite'])->name('diagnostic.team.show');
Route::post('/team/{token}', [DiagnosticController::class, 'submitTeamInvite'])->name('diagnostic.team.submit');

// Multi-Company Client Account, Historical Tracking, Document Vault & Action Tracker
Route::get('/portal', [ClientPortalController::class, 'index'])->name('portal.index');
Route::post('/portal/companies', [ClientPortalController::class, 'storeCompany'])->name('portal.companies.store');
Route::post('/portal/actions/{actionItem}/log', [ClientPortalController::class, 'logActionProgress'])->name('portal.actions.log');
Route::delete('/portal/documents/{document}', [ClientPortalController::class, 'purgeDocument'])->name('portal.documents.purge');

// Consultant Dashboard, Consultant Review Mode & Framework Editor (Protected by Role-Based Access Control)
Route::prefix('consultant')->middleware(EnsureRole::class.':admin,consultant')->group(function () {
    Route::get('/', [ConsultantController::class, 'dashboard'])->name('consultant.dashboard');
    Route::get('/review/{ulid}', [ConsultantController::class, 'reviewShow'])->name('consultant.review');
    Route::post('/review/{ulid}', [ConsultantController::class, 'reviewUpdate'])->name('consultant.review.update');
    Route::post('/review/{ulid}/regenerate', [ConsultantController::class, 'regenerateAi'])->name('consultant.review.regenerate');
    Route::post('/review/{ulid}/actions', [ConsultantController::class, 'addActionItem'])->name('consultant.review.actions.add');
    Route::post('/consultations/{consultation}/status', [ConsultantController::class, 'updateConsultationStatus'])->name('consultant.consultations.status');
    Route::get('/framework', [ConsultantController::class, 'frameworkEditor'])
        ->middleware(EnsureRole::class.':admin')
        ->name('consultant.framework');
    Route::post('/framework', [ConsultantController::class, 'frameworkPublish'])
        ->middleware(EnsureRole::class.':admin')
        ->name('consultant.framework.publish');
    Route::post('/framework/{version}/activate', [ConsultantController::class, 'frameworkActivate'])
        ->middleware(EnsureRole::class.':admin')
        ->name('consultant.framework.activate');
});

// Admin Console (Dynamic Text, Component Visibility Toggles, User Directory excluding Developers)
Route::prefix('admin')->middleware(EnsureRole::class.':admin')->group(function () {
    Route::get('/', [DeveloperController::class, 'index'])->name('admin.console');
    Route::post('/text', [DeveloperController::class, 'updateText'])->name('admin.text.update');
    Route::post('/visibility', [DeveloperController::class, 'updateVisibility'])->name('admin.visibility.update');
    Route::post('/reset', [DeveloperController::class, 'resetDefaults'])->name('admin.reset');
    Route::post('/users', [DeveloperController::class, 'storeUser'])->name('admin.users.store');
    Route::post('/users/{user}', [DeveloperController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/auto-assign-settings', [DeveloperController::class, 'updateAutoAssignmentSettings'])->name('admin.assignment.settings');
    Route::post('/companies/{company}/assign', [DeveloperController::class, 'assignCompanyConsultant'])->name('admin.companies.assign');
    Route::post('/companies/auto-assign-all', [DeveloperController::class, 'runBatchAutoAssign'])->name('admin.companies.auto_assign_all');
    Route::post('/menu', [DeveloperController::class, 'storeMenuItem'])->name('admin.menu.store');
    Route::post('/menu/{item}', [DeveloperController::class, 'updateMenuItem'])->name('admin.menu.update');
    Route::post('/menu/{item}/toggle-visibility', [DeveloperController::class, 'toggleMenuVisibility'])->name('admin.menu.toggle_visibility');
    Route::post('/menu/{item}/toggle-active', [DeveloperController::class, 'toggleMenuActive'])->name('admin.menu.toggle_active');
    Route::post('/menu/{item}/delete', [DeveloperController::class, 'destroyMenuItem'])->name('admin.menu.delete');
    Route::post('/menu-reset', [DeveloperController::class, 'resetMenuDefaults'])->name('admin.menu.reset');
});

// Developer Super-Account Console (Dynamic Text, Component Visibility Toggles, Full User Directory & System Control)
Route::prefix('developer')->middleware(EnsureRole::class.':developer,admin')->group(function () {
    Route::get('/', [DeveloperController::class, 'index'])->name('developer.console');
    Route::post('/text', [DeveloperController::class, 'updateText'])->name('developer.text.update');
    Route::post('/visibility', [DeveloperController::class, 'updateVisibility'])->name('developer.visibility.update');
    Route::post('/reset', [DeveloperController::class, 'resetDefaults'])->name('developer.reset');
    Route::post('/users', [DeveloperController::class, 'storeUser'])->name('developer.users.store');
    Route::post('/users/{user}', [DeveloperController::class, 'updateUser'])->name('developer.users.update');
    Route::post('/auto-assign-settings', [DeveloperController::class, 'updateAutoAssignmentSettings'])->name('developer.assignment.settings');
    Route::post('/companies/{company}/assign', [DeveloperController::class, 'assignCompanyConsultant'])->name('developer.companies.assign');
    Route::post('/companies/auto-assign-all', [DeveloperController::class, 'runBatchAutoAssign'])->name('developer.companies.auto_assign_all');
    Route::post('/menu', [DeveloperController::class, 'storeMenuItem'])->name('developer.menu.store');
    Route::post('/menu/{item}', [DeveloperController::class, 'updateMenuItem'])->name('developer.menu.update');
    Route::post('/menu/{item}/toggle-visibility', [DeveloperController::class, 'toggleMenuVisibility'])->name('developer.menu.toggle_visibility');
    Route::post('/menu/{item}/toggle-active', [DeveloperController::class, 'toggleMenuActive'])->name('developer.menu.toggle_active');
    Route::post('/menu/{item}/delete', [DeveloperController::class, 'destroyMenuItem'])->name('developer.menu.delete');
    Route::post('/menu-reset', [DeveloperController::class, 'resetMenuDefaults'])->name('developer.menu.reset');
});

// Authenticated User Profile (All Roles: Client, Consultant, Admin, Developer)
Route::prefix('profile')->middleware('auth')->group(function () {
    Route::get('/', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/details', [ProfileController::class, 'updateDetails'])->name('profile.update');
    Route::post('/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

// Authentication & Quick Role Switcher
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/switch-role', [AuthController::class, 'quickSwitch'])->name('auth.switch');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
