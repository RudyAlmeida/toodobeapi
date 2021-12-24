<?php

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Services\PipeDriveService;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('not-authenticated', function () {
    return response()->json('not authenticated', 403);
})->name('login');


Route::get('/', function (Request $request) {
     return response()->json([
        'service' => 'Toodo.be API',
        'status' => 'OK'
    ], 200);
});

Route::post('register', 'AuthController@register');
Route::post('login', 'AuthController@login');

Route::post('webhook', 'WeebhookController@webhookHandle');

Route::get('invite-track/{trackCode}', 'InvitesController@track');

Route::get('properties/list', 'PropertiesController@index');

Route::group(['prefix' => 'password-reset'], function () {
    Route::post('create', 'PasswordResetController@create');
    Route::get('find/{token}', 'PasswordResetController@find');
    Route::post('reset', 'PasswordResetController@reset');
});

Route::get('ficha/{id}', 'RegistrationFormsController@showPrint');


Route::get('verify-email/{token}', 'AuthController@confirmEmail');
Route::get('resend-verification-email/{email}', 'AuthController@resendVerification');

//Rotas autenticadas
Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', 'AuthController@logout');
    Route::get('dashboard', 'DashboardController@index');

    Route::group(['prefix' => 'registration-form'], function () {
        Route::get('list', 'RegistrationFormsController@index');
        Route::get('{id}', 'RegistrationFormsController@show');
        Route::post('/create', 'RegistrationFormsController@create');
        Route::post('/update/{id}', 'RegistrationFormsController@update');
        Route::delete('/destroy/{id}', 'RegistrationFormsController@destroy');
    });

    Route::group(['prefix' => 'documents-request'], function () {
        Route::get('list', 'DocumentationRequestsController@index');
        Route::get('{id}', 'DocumentationRequestsController@show');
        Route::post('/create', 'DocumentationRequestsController@create');
        Route::post('/update/{id}', 'DocumentationRequestsController@update');
        Route::delete('/destroy/{id}', 'DocumentationRequestsController@destroy');
    });

    Route::group(['prefix' => 'invite'], function () {
        Route::get('list', 'InvitesController@index');
        Route::post('/create', 'InvitesController@create');
        Route::delete('/destroy/{id}', 'InvitesController@destroy');
    });

    Route::group(['prefix' => 'profile'], function () {
        Route::post('/update', 'AuthController@selfUpdate');
        Route::post('/upload-photo', 'UserController@uploadProfilePhoto');
    });

    Route::group(['prefix' => 'projects'], function () {
        Route::get('list', 'ProjectFormsController@index');
        Route::get('{id}', 'ProjectFormsController@show');
        Route::post('/create', 'ProjectFormsController@create');
        Route::post('/update/{id}', 'ProjectFormsController@update');
        Route::delete('/destroy/{id}', 'ProjectFormsController@destroy');
    });

    Route::group(['prefix' => 'properties'], function () {
        Route::get('{id}', 'PropertiesController@show');
        Route::post('/create', 'PropertiesController@create');
        Route::post('/update/{id}', 'PropertiesController@update');
        Route::delete('/destroy/{id}', 'PropertiesController@destroy');
    });

    Route::group(['prefix' => 'bank'], function () {
        Route::get('list', 'BankImportationsController@index');
        Route::get('{id}', 'BankImportationsController@show');
        Route::post('/create', 'BankImportationsController@create');
        Route::post('/update/{id}', 'BankImportationsController@update');
        Route::delete('/destroy/{id}', 'BankImportationsController@destroy');
    });

    Route::group(['prefix' => 'marketing'], function () {
        Route::get('/list', 'Marketing@index');
    });


    Route::group(['prefix' => 'billings'], function () {
        Route::get('/list', 'BillingsController@index');
        Route::get('/list-by-subscription/{payment_gateway_subscription_id}', 'BillingsController@indexBySubscription');
        Route::get('{id}', 'BillingsController@show');
        Route::post('/create', 'BillingsController@create');
        Route::post('/update/{id}', 'BillingsController@update');
        Route::delete('/destroy/{id}', 'BillingsController@destroy');
    });

    Route::group(['prefix' => 'subscriptions'], function () {
        Route::get('/list', 'SubscriptionsController@index');
        Route::get('{id}', 'SubscriptionsController@show');
        Route::post('/create', 'SubscriptionsController@create');
        Route::post('/update/{id}', 'SubscriptionsController@update');
        Route::delete('/destroy/{id}', 'SubscriptionsController@destroy');
    });

    // Rotas de ADMIN
    Route::group(['prefix' => 'admin'], function () {
        Route::group(['prefix' => 'users'], function () {
            Route::get('list', 'UserController@index');
            Route::get('{id}', 'UserController@show');
            Route::post('/create', 'UserController@create');
            Route::post('/update/{id}', 'UserController@update');
            Route::delete('/destroy/{id}', 'UserController@destroy');
        });
    });

});



