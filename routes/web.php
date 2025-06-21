<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuranController;
use App\Http\Controllers\User\BookmarkController;
use App\Http\Controllers\User\PreferenceController;
use App\Http\Controllers\User\JuzController;
use App\Http\Controllers\User\FavoriteSuratController;
use App\Http\Controllers\CustomJuzController;
use App\Http\Controllers\Admin\AyatManagementController;
use App\Http\Controllers\Admin\EditionController;
use App\Http\Controllers\Admin\JuzManagementController;
use App\Http\Controllers\Admin\SuratManagementController;
use App\Http\Controllers\Admin\AudioEditionController;
use App\Http\Controllers\SearchController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// --- Public Routes ---
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});


// --- Authenticated User Routes ---
Route::middleware(['auth'])->group(function () {
    // Core Quran Reading
    Route::get('/', [QuranController::class, 'index'])->name('quran.index');
    Route::get('/home', [QuranController::class, 'index'])->name('home');
    Route::get('/surah/{number}', [QuranController::class, 'showSurah'])->name('surah.show');
    Route::get('/ayah/{surah}/{ayah}', [QuranController::class, 'showAyah'])->name('ayah.show');
    Route::post('/last-read', [QuranController::class, 'saveLastRead'])->name('last-read.save');
    Route::get('/quran/juz/{number}', [QuranController::class, 'showJuz'])->name('quran.juz');

    // Bookmarks
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::post('/bookmarks/toggle', [QuranController::class, 'toggleBookmark'])->name('bookmarks.toggle');
    Route::delete('/bookmarks/{bookmark}', [BookmarkController::class, 'destroy'])->name('bookmarks.destroy');
    
    // Notes (Centralized in QuranController)
    Route::get('/notes', [QuranController::class, 'showNotes'])->name('notes.show');
    Route::post('/notes', [QuranController::class, 'addNote'])->name('notes.add');
    Route::put('/notes/{id}', [QuranController::class, 'updateNote'])->name('notes.update');
    Route::delete('/notes/{id}', [QuranController::class, 'deleteNote'])->name('notes.delete');
    Route::post('/notes/save', [QuranController::class, 'saveNote'])->name('notes.save');
    
    // Rute untuk menghapus 'Terakhir Dibaca'
    Route::delete('/last-read', [QuranController::class, 'deleteLastRead'])->name('last-read.delete');

    // Favorite Surats
    Route::get('/quran/favorites', [FavoriteSuratController::class, 'index'])->name('quran.favorites');
    Route::post('/quran/favorites/{suratNumber}', [FavoriteSuratController::class, 'toggleFavorite'])->name('quran.favorites.toggle');

    // Custom Juz
    Route::resource('juz', CustomJuzController::class);
    
    // --- Search History Routes ---
    Route::get('/search/history', [SearchController::class, 'index'])->name('search.history');
    Route::post('/search/history', [SearchController::class, 'store'])->name('search.history.store');
    Route::delete('/search/history/{searchHistory}', [SearchController::class, 'destroy'])->name('search.history.destroy');

    // User Preferences
    Route::get('user/preferences/language', [PreferenceController::class, 'showLanguageOptions'])->name('user.preferences.language.show');
    Route::post('user/preferences/language', [PreferenceController::class, 'updateLanguagePreference'])->name('user.preferences.language.update');

    // User Juz Reading Progress
    Route::get('user/juz', [JuzController::class, 'index'])->name('user.juz.index');
    Route::get('user/juz/{juzNumber}', [JuzController::class, 'showJuzContent'])->name('user.juz.show_content');
    Route::post('user/juz/{juzNumber}/update-progress', [JuzController::class, 'updateProgress'])->name('user.juz.update_progress');
    Route::post('user/juz/{juzNumber}/mark-completed', [JuzController::class, 'markAsCompleted'])->name('user.juz.mark_completed');
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});


// --- Admin Routes ---
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Ayat & Note Management
    Route::get('surah/{surahNumber}/ayats', [AyatManagementController::class, 'showSurahAyats'])->name('ayats.show_surah_ayats');
    Route::post('admin-notes/{apiAyatIdentifier}', [AyatManagementController::class, 'storeAdminNote'])->name('admin_notes.store');
    Route::put('admin-notes/{note}', [AyatManagementController::class, 'updateAdminNote'])->name('admin_notes.update');
    Route::delete('admin-notes/{note}', [AyatManagementController::class, 'destroyAdminNote'])->name('admin_notes.destroy');

    // Keyword Management
    Route::post('global-keywords/{apiEntityIdentifier}/{entityType}', [AyatManagementController::class, 'storeGlobalKeyword'])->name('global_keywords.store');
    Route::delete('global-keywords/{keyword}', [AyatManagementController::class, 'destroyGlobalKeyword'])->name('global_keywords.destroy');

    // Edition Management
    Route::get('editions', [EditionController::class, 'index'])->name('editions.index');
    Route::post('editions/sync', [EditionController::class, 'syncAndStoreEditions'])->name('editions.sync');
    Route::post('editions/add-from-api', [EditionController::class, 'addApiEditionToLocal'])->name('editions.add_from_api');
    Route::patch('editions/{edition}/toggle-availability', [EditionController::class, 'toggleUserAvailability'])->name('editions.toggle_availability');

    // Juz Management
    Route::get('juz-management', [JuzManagementController::class, 'index'])->name('juz_management.index');
    Route::get('juz-management/{juzNumber}/edit', [JuzManagementController::class, 'edit'])->name('juz_management.edit');
    Route::put('juz-management/{juzNumber}', [JuzManagementController::class, 'update'])->name('juz_management.update');

    // Surat Management
    Route::get('surat-management', [SuratManagementController::class, 'index'])->name('surats.index');
    Route::get('surat-management/{surahNumber}/edit', [SuratManagementController::class, 'edit'])->name('surats.edit');
    Route::put('surat-management/{surahNumber}', [SuratManagementController::class, 'update'])->name('surats.update');

    // Audio Edition Management
    Route::get('audio-editions', [AudioEditionController::class, 'index'])->name('audio_editions.index');
    Route::post('audio-editions', [AudioEditionController::class, 'store'])->name('audio_editions.store');
    Route::patch('audio-editions/{edition}/toggle-availability', [AudioEditionController::class, 'toggleAvailability'])->name('audio_editions.toggle_availability');
    Route::delete('audio-editions/{edition}', [AudioEditionController::class, 'destroy'])->name('audio_editions.destroy');
});

// Auth scaffolding routes (if you have them)
require __DIR__.'/auth.php';
