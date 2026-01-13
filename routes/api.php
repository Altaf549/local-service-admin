<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ServiceCategoryController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\PujaTypeController;
use App\Http\Controllers\Api\PujaController;
use App\Http\Controllers\Api\ServicemanController;
use App\Http\Controllers\Api\BrahmanController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ProfileController;

// Public Routes
Route::get('/home', [HomeController::class, 'index']);

// Authentication Routes
Route::post('/user/register', [AuthController::class, 'userRegister']);
Route::post('/user/login', [AuthController::class, 'userLogin']);
Route::post('/serviceman/register', [AuthController::class, 'servicemanRegister']);
Route::post('/serviceman/login', [AuthController::class, 'servicemanLogin']);
Route::post('/brahman/register', [AuthController::class, 'brahmanRegister']);
Route::post('/brahman/login', [AuthController::class, 'brahmanLogin']);

// Public Data Routes
Route::get('/service-categories', [ServiceCategoryController::class, 'index']);
Route::get('/service-categories/{id}', [ServiceCategoryController::class, 'show']);
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);
Route::get('/services/by-category/{categoryId}', [ServiceController::class, 'getServicesByCategory']);
Route::get('/puja-types', [PujaTypeController::class, 'index']);
Route::get('/puja-types/{id}', [PujaTypeController::class, 'show']);
Route::get('/pujas', [PujaController::class, 'index']);
Route::get('/pujas/{id}', [PujaController::class, 'show']);
Route::get('/pujas/by-type/{typeId}', [PujaController::class, 'getPujasByType']);
Route::get('/servicemen', [ServicemanController::class, 'index']);
Route::get('/servicemen/details/{id}', [ServicemanController::class, 'getDetails']);
Route::get('/brahmans', [BrahmanController::class, 'index']);
Route::get('/brahmans/details/{id}', [BrahmanController::class, 'getDetails']);

// Protected Routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Delete Account Routes
    Route::delete('/user/delete-account', [AuthController::class, 'deleteUserAccount']);
    Route::delete('/serviceman/delete-account', [AuthController::class, 'deleteServicemanAccount']);
    Route::delete('/brahman/delete-account', [AuthController::class, 'deleteBrahmanAccount']);
    
    // User Profile
    Route::post('/user/profile/update', [ProfileController::class, 'updateProfile']);
    
    // Service Price Update (Admin only or authorized)
    Route::post('/services/price/update/{id}', [ServiceController::class, 'updatePrice']);
    
    // Puja Price and Material File Update (Admin only or authorized)
    Route::post('/pujas/price/update/{id}', [PujaController::class, 'updatePrice']);
    
    // Serviceman Profile Management
    Route::post('/serviceman/profile/update', [ServicemanController::class, 'updateProfile']);
    Route::get('/serviceman/experiences', [ServicemanController::class, 'getExperiences']);
    Route::get('/serviceman/achievements', [ServicemanController::class, 'getAchievements']);
    Route::post('/serviceman/experience/add', [ServicemanController::class, 'addExperience']);
    Route::put('/serviceman/experience/{id}', [ServicemanController::class, 'updateExperience']);
    Route::delete('/serviceman/experience/{id}', [ServicemanController::class, 'deleteExperience']);
    Route::post('/serviceman/achievement/add', [ServicemanController::class, 'addAchievement']);
    Route::put('/serviceman/achievement/{id}', [ServicemanController::class, 'updateAchievement']);
    Route::delete('/serviceman/achievement/{id}', [ServicemanController::class, 'deleteAchievement']);
    
    // Brahman Profile Management
    Route::post('/brahman/profile/update', [BrahmanController::class, 'updateProfile']);
    Route::get('/brahman/experiences', [BrahmanController::class, 'getExperiences']);
    Route::get('/brahman/achievements', [BrahmanController::class, 'getAchievements']);
    Route::post('/brahman/experience/add', [BrahmanController::class, 'addExperience']);
    Route::put('/brahman/experience/{id}', [BrahmanController::class, 'updateExperience']);
    Route::delete('/brahman/experience/{id}', [BrahmanController::class, 'deleteExperience']);
    Route::post('/brahman/achievement/add', [BrahmanController::class, 'addAchievement']);
    Route::put('/brahman/achievement/{id}', [BrahmanController::class, 'updateAchievement']);
    Route::delete('/brahman/achievement/{id}', [BrahmanController::class, 'deleteAchievement']);
});

