<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactMergeController;
use App\Http\Controllers\CustomFieldDefinitionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
 */

Route::get('/', function () {
    return redirect()->route('contacts.index');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile Management (from Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('contacts', ContactController::class);
    Route::get('/contacts/{contact}', [ContactController::class, 'show'])
        ->name('contacts.show')
        ->withTrashed();

    Route::get('contacts-data', [ContactController::class, 'getContactsData'])->name('contacts.data');

    Route::resource('custom-fields', CustomFieldDefinitionController::class)->except(['show']);

    Route::get('contacts/merge/init/{contact1}/{contact2?}', [ContactMergeController::class, 'initiateMerge'])->name('contacts.merge.init');

    Route::post('contacts/merge/confirm', [ContactMergeController::class, 'confirmMerge'])->name('contacts.merge.confirm');

    Route::get('trashed/contacts', [ContactController::class, 'trashedContacts'])->name('contacts.trashed');
    Route::post('contacts/{contact}/restore', [ContactController::class, 'restoreContact'])->name('contacts.restore');
    Route::delete('contacts/{contact}/force-delete', [ContactController::class, 'forceDeleteContact'])->name('contacts.force_delete');

    Route::resource('audits', AuditController::class);
});

// Include Breeze's auth routes
require __DIR__ . '/auth.php';