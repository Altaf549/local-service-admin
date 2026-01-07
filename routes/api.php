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
Route::get('/puja-types', [PujaTypeController::class, 'index']);
Route::get('/puja-types/{id}', [PujaTypeController::class, 'show']);
Route::get('/pujas', [PujaController::class, 'index']);
Route::get('/pujas/{id}', [PujaController::class, 'show']);
Route::get('/servicemen', [ServicemanController::class, 'index']);
Route::get('/brahmans', [BrahmanController::class, 'index']);

// Protected Routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // User Profile
    Route::post('/user/profile/update', [ProfileController::class, 'updateProfile']);
    
    // Service Price Update (Admin only or authorized)
    Route::post('/services/{id}/price/update', [ServiceController::class, 'updatePrice']);
    
    // Puja Price and Material File Update (Admin only or authorized)
    Route::post('/pujas/{id}/price/update', [PujaController::class, 'updatePrice']);
    
    // Serviceman Profile Management
    Route::post('/serviceman/profile/update', [ServicemanController::class, 'updateProfile']);
    Route::post('/serviceman/experience/add', [ServicemanController::class, 'addExperience']);
    Route::post('/serviceman/achievement/add', [ServicemanController::class, 'addAchievement']);
    
    // Brahman Profile Management
    Route::post('/brahman/profile/update', [BrahmanController::class, 'updateProfile']);
    Route::post('/brahman/experience/add', [BrahmanController::class, 'addExperience']);
    Route::post('/brahman/achievement/add', [BrahmanController::class, 'addAchievement']);
});

