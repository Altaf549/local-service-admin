<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\PujaTypeController;
use App\Http\Controllers\Admin\PujaController;
use App\Http\Controllers\Admin\ServicemanController;
use App\Http\Controllers\Admin\BrahmanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ServicemanServicePriceController;
use App\Http\Controllers\Admin\BrahmanPujaPriceController;
use App\Http\Controllers\Admin\ServicemanExperienceController;
use App\Http\Controllers\Admin\ServicemanAchievementController;
use App\Http\Controllers\Admin\BrahmanExperienceController;
use App\Http\Controllers\Admin\BrahmanAchievementController;
use App\Http\Controllers\PageController;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Public Pages
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/terms-and-conditions', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/about-us', [PageController::class, 'about'])->name('pages.about');

// Admin Authentication
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Admin Protected Routes
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Service Categories
    Route::resource('service-categories', ServiceCategoryController::class);
    Route::post('service-categories/{id}/toggle-status', [ServiceCategoryController::class, 'toggleStatus'])->name('service-categories.toggle-status');
    
    // Services
    Route::resource('services', ServiceController::class);
    Route::post('services/{id}/toggle-status', [ServiceController::class, 'toggleStatus'])->name('services.toggle-status');
    
    // Puja Types
    Route::resource('puja-types', PujaTypeController::class);
    Route::post('puja-types/{id}/toggle-status', [PujaTypeController::class, 'toggleStatus'])->name('puja-types.toggle-status');
    
    // Pujas
    Route::resource('pujas', PujaController::class);
    Route::post('pujas/{id}/toggle-status', [PujaController::class, 'toggleStatus'])->name('pujas.toggle-status');
    
    // Servicemen
    Route::resource('servicemen', ServicemanController::class);
    Route::post('servicemen/{id}/toggle-status', [ServicemanController::class, 'toggleStatus'])->name('servicemen.toggle-status');
    Route::post('servicemen/{id}/assign-services', [ServicemanController::class, 'assignServices'])->name('servicemen.assign-services');
    
    // Brahmans
    Route::resource('brahmans', BrahmanController::class);
    Route::post('brahmans/{id}/toggle-status', [BrahmanController::class, 'toggleStatus'])->name('brahmans.toggle-status');
    
    // Users
    Route::resource('users', UserController::class);
    Route::post('users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    
    // Banners
    Route::resource('banners', BannerController::class);
    Route::post('banners/{id}/toggle-status', [BannerController::class, 'toggleStatus'])->name('banners.toggle-status');
    
    // Serviceman Service Prices
    Route::get('serviceman-service-prices', [ServicemanServicePriceController::class, 'index'])->name('serviceman-service-prices.index');
    Route::get('serviceman-service-prices/data', [ServicemanServicePriceController::class, 'getData'])->name('serviceman-service-prices.data');
    Route::get('serviceman-service-prices/{id}', [ServicemanServicePriceController::class, 'show'])->name('serviceman-service-prices.show');
    Route::post('serviceman-service-prices/{id}', [ServicemanServicePriceController::class, 'update'])->name('serviceman-service-prices.update');
    Route::post('serviceman-service-prices/{id}/delete', [ServicemanServicePriceController::class, 'destroy'])->name('serviceman-service-prices.destroy');
    
    // Brahman Puja Prices
    Route::get('brahman-puja-prices', [BrahmanPujaPriceController::class, 'index'])->name('brahman-puja-prices.index');
    Route::get('brahman-puja-prices/data', [BrahmanPujaPriceController::class, 'getData'])->name('brahman-puja-prices.data');
    Route::get('brahman-puja-prices/{id}', [BrahmanPujaPriceController::class, 'show'])->name('brahman-puja-prices.show');
    Route::post('brahman-puja-prices/{id}', [BrahmanPujaPriceController::class, 'update'])->name('brahman-puja-prices.update');
    Route::post('brahman-puja-prices/{id}/delete', [BrahmanPujaPriceController::class, 'destroy'])->name('brahman-puja-prices.destroy');
    
    // Serviceman Experiences
    Route::get('serviceman-experiences', [ServicemanExperienceController::class, 'index'])->name('serviceman-experiences.index');
    Route::get('serviceman-experiences/data', [ServicemanExperienceController::class, 'getData'])->name('serviceman-experiences.data');
    Route::get('serviceman-experiences/{id}', [ServicemanExperienceController::class, 'show'])->name('serviceman-experiences.show');
    Route::post('serviceman-experiences/{id}', [ServicemanExperienceController::class, 'update'])->name('serviceman-experiences.update');
    Route::post('serviceman-experiences/{id}/delete', [ServicemanExperienceController::class, 'destroy'])->name('serviceman-experiences.destroy');
    
    // Serviceman Achievements
    Route::get('serviceman-achievements', [ServicemanAchievementController::class, 'index'])->name('serviceman-achievements.index');
    Route::get('serviceman-achievements/data', [ServicemanAchievementController::class, 'getData'])->name('serviceman-achievements.data');
    Route::get('serviceman-achievements/{id}', [ServicemanAchievementController::class, 'show'])->name('serviceman-achievements.show');
    Route::post('serviceman-achievements/{id}', [ServicemanAchievementController::class, 'update'])->name('serviceman-achievements.update');
    Route::post('serviceman-achievements/{id}/delete', [ServicemanAchievementController::class, 'destroy'])->name('serviceman-achievements.destroy');
    
    // Brahman Experiences
    Route::get('brahman-experiences', [BrahmanExperienceController::class, 'index'])->name('brahman-experiences.index');
    Route::get('brahman-experiences/data', [BrahmanExperienceController::class, 'getData'])->name('brahman-experiences.data');
    Route::get('brahman-experiences/{id}', [BrahmanExperienceController::class, 'show'])->name('brahman-experiences.show');
    Route::post('brahman-experiences/{id}', [BrahmanExperienceController::class, 'update'])->name('brahman-experiences.update');
    Route::post('brahman-experiences/{id}/delete', [BrahmanExperienceController::class, 'destroy'])->name('brahman-experiences.destroy');
    
    // Brahman Achievements
    Route::get('brahman-achievements', [BrahmanAchievementController::class, 'index'])->name('brahman-achievements.index');
    Route::get('brahman-achievements/data', [BrahmanAchievementController::class, 'getData'])->name('brahman-achievements.data');
    Route::get('brahman-achievements/{id}', [BrahmanAchievementController::class, 'show'])->name('brahman-achievements.show');
    Route::post('brahman-achievements/{id}', [BrahmanAchievementController::class, 'update'])->name('brahman-achievements.update');
    Route::post('brahman-achievements/{id}/delete', [BrahmanAchievementController::class, 'destroy'])->name('brahman-achievements.destroy');
    
    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::get('settings/banner', [SettingController::class, 'banner'])->name('settings.banner');
    Route::get('settings/terms', [SettingController::class, 'terms'])->name('settings.terms');
    Route::get('settings/privacy', [SettingController::class, 'privacy'])->name('settings.privacy');
    Route::get('settings/about', [SettingController::class, 'about'])->name('settings.about');
    Route::post('settings/terms', [SettingController::class, 'updateTerms'])->name('settings.update-terms');
    Route::post('settings/privacy', [SettingController::class, 'updatePrivacy'])->name('settings.update-privacy');
    Route::post('settings/about', [SettingController::class, 'updateAbout'])->name('settings.update-about');
});
