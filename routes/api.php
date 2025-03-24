<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OpenAIConfigController;
use App\Http\Controllers\PlantTypeController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuestionnaireController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserReportController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/resend-email', [AuthController::class, 'resendEmail']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-forgot-email', [AuthController::class, 'verifyForgotEmail']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Route::prefix('data')->group(function () {});

// Protected Routes (Require Authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/stats', [StatsController::class, 'adminStats']);
    Route::prefix('openai')->group(function () {
        Route::post('/manage-config', [OpenAIConfigController::class, 'manageOpenAIConfig']);
        Route::get('/view-config', [OpenAIConfigController::class, 'viewOpenAIConfig']);
    });

    // Plant Type Routes
    Route::prefix('plant-types')->group(function () {
        Route::get('/all', [PlantTypeController::class, 'allPlantTypes']);
        Route::post('/manage', [PlantTypeController::class, 'managePlantType']);
        Route::delete('delete/{id}', [PlantTypeController::class, 'deletePlantType']);
        Route::get('/{id?}', [PlantTypeController::class, 'viewPlantType']);
    });

    // Question Routes
    // Route::prefix('questions')->group(function () {
    //     Route::post('/manage', [QuestionController::class, 'manageQuestion']);
    //     Route::delete('/{id}', [QuestionController::class, 'deleteQuestion']);
    //     Route::get('/plant-type/{plantTypeId}', [QuestionController::class, 'viewQuestion']);
    // });

    // // Answer Routes
    // Route::prefix('answers')->group(function () {
    //     Route::post('/manage', [AnswerController::class, 'manageAnswer']);
    //     Route::delete('/{id}', [AnswerController::class, 'deleteAnswer']);
    //     Route::get('/question/{questionId}', [AnswerController::class, 'viewAnswer']); // Updated route
    // });
    Route::prefix('questionnaire')->group(function () {
        Route::post('/manage', [QuestionnaireController::class, 'storeOrUpdateQuestionnaire']); // Add/Update
        Route::delete('/{plantTypeId}', [QuestionnaireController::class, 'deleteQuestionnaire']); // Delete entire questionnaire
        Route::get('/plant-type/{plantTypeId}', [QuestionnaireController::class, 'viewQuestionnaire']); // Fetch questionnaire
        Route::get('/all', [QuestionnaireController::class, 'getAllQuestionnaires']);
    });
    

    // User Report Routes
    Route::prefix('user-reports')->group(function () {
        Route::post('/manage', [UserReportController::class, 'manageUserReport']);
        Route::delete('delete/{id}', [UserReportController::class, 'deleteUserReport']);
        Route::get('/{id?}', [UserReportController::class, 'viewUserReport']);
    });

    Route::prefix('userdata')->group(function () {
        Route::get('/get/{id}', [UserController::class, 'getUser']); // Get single user with company
        Route::get('/get-all', [UserController::class, 'getAllUsers']); // Get all users with companies
        Route::put('/toggle-status/{id}', [UserController::class, 'toggleUserStatus']); // Activate/Deactivate user
        Route::put('/make-subadmin/{id}', [UserController::class, 'makeSubadmin']); // Activate/Deactivate user
        // Route::delete('/delete/{id}', [UserController::class, 'deleteUser']); // Delete user
    });
});

Route::middleware(['auth:sanctum', 'user'])->prefix('user')->group(function () {
    Route::prefix('plant-types')->group(function () {
        Route::get('/all', [PlantTypeController::class, 'allPlantTypes']);
        Route::get('/{id?}', [PlantTypeController::class, 'viewPlantType']);
    });
    Route::prefix('questionnaire')->group(function () {
        Route::get('/plant-type/{plantTypeId}', [QuestionnaireController::class, 'viewQuestionnaire']); // Fetch questionnaire
        // Route::get('/all', [QuestionnaireController::class, 'getAllQuestionnaires']);
    });
    Route::prefix('openai')->group(function () {
        Route::post('/generate-response', [OpenAIConfigController::class, 'generateContent']);
    });
    Route::prefix('user-reports')->group(function () {
        Route::get('/all', [UserReportController::class, 'viewUserReports']);
        Route::delete('delete/{id}', [UserReportController::class, 'deleteUserReport']);
    });
    Route::get('/stats', [StatsController::class, 'userStats']);
});
