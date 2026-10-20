<?php

use App\Http\Controllers\Auth\FirebaseAuthController;
use App\Http\Controllers\InvoicePdfController;
use App\Livewire\Dashboard\BentoOverview;
use App\Livewire\Invoices\InvoiceIndex;
use App\Livewire\Invoices\SplitInvoiceBuilder;
use App\Livewire\Projects\ProjectIndex;
use App\Livewire\Projects\ProjectWorkspace;
use App\Livewire\Settings\SettingsManager;
use App\Livewire\Timesheet\TimesheetManager;
use Illuminate\Support\Facades\Route;

// Authentication
Route::get('/login', [FirebaseAuthController::class, 'login'])->name('login')->middleware('guest');
Route::post('/auth/firebase/callback', [FirebaseAuthController::class, 'callback'])->name('auth.firebase.callback');
Route::post('/logout', [FirebaseAuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected application routes
Route::middleware('auth')->group(function () {
    // Dashboard Bento Overview
    Route::get('/', BentoOverview::class)->name('dashboard');

    // Timesheet Manager
    Route::get('/timesheet', TimesheetManager::class)->name('timesheet');

    // Projects & Kanban Workspace
    Route::get('/projects', ProjectIndex::class)->name('projects.index');
    Route::get('/projects/{slug}', ProjectWorkspace::class)->name('projects.workspace');

    // Invoices & Split-Screen Builder
    Route::get('/invoices', InvoiceIndex::class)->name('invoices.index');
    Route::get('/invoices/new', SplitInvoiceBuilder::class)->name('invoices.builder');

    // PDF Streaming & Download
    Route::get('/invoices/{invoice}/pdf', [InvoicePdfController::class, 'show'])->name('invoices.pdf');

    // System, Currency & Profile Settings
    Route::get('/settings', SettingsManager::class)->name('settings');
    Route::get('/profile', SettingsManager::class)->name('profile');
});
