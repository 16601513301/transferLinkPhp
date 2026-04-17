<?php

use BaseApi\App;
use App\Controllers\HealthController;
use App\Controllers\HomeController;
use App\Controllers\BenchmarkController;
use App\Controllers\OpenApiController;
use App\Controllers\StreamController;
use App\Controllers\Api\LoginController;
use App\Controllers\Api\LogoutController;
use App\Controllers\Api\ProfileController;
use App\Controllers\Api\UserAgreementController;
use App\Controllers\Api\QrLoginConfirmController;
use App\Controllers\Api\QrLoginCreateController;
use App\Controllers\Api\QrLoginScanController;
use App\Controllers\Api\QrLoginStatusController;
use App\Controllers\Api\SignupController;
use App\Controllers\Api\SmsCodeController;
use App\Controllers\Api\SmsLoginController;
use App\Controllers\Api\TransferConvertController;
use App\Controllers\Api\TransferShortLinkController;
use BaseApi\Http\Middleware\RateLimitMiddleware;
use App\Middleware\UserApiTokenAuthMiddleware;

$router = App::router();

// ================================
// Public Endpoints (No Auth)
// ================================

$router->post('/', [
    [HomeController::class, 'get'],
]);

// Health check
$router->post('/health', [
    RateLimitMiddleware::class => ['limit' => '60/1m'],
    [HealthController::class, 'get'],
]);

// Benchmark endpoint (no middleware for performance testing)
$router->post('/benchmark', [
    [BenchmarkController::class, 'get'],
]);

// Authentication endpoints
$router->post('/auth/signup', [
    RateLimitMiddleware::class => ['limit' => '5/1m'],
    SignupController::class,
]);

$router->post('/auth/login', [
    RateLimitMiddleware::class => ['limit' => '10/1m'],
    LoginController::class,
]);

$router->post('/auth/sms-code', [
    RateLimitMiddleware::class => ['limit' => '5/1m'],
    SmsCodeController::class,
]);

$router->post('/auth/sms-login', [
    RateLimitMiddleware::class => ['limit' => '10/1m'],
    SmsLoginController::class,
]);

$router->post('/auth/logout', [
    UserApiTokenAuthMiddleware::class,
    LogoutController::class,
]);

$router->post('/auth/qr-login', [
    RateLimitMiddleware::class => ['limit' => '20/1m'],
    QrLoginCreateController::class,
]);

$router->post('/auth/qr-login/status', [
    RateLimitMiddleware::class => ['limit' => '60/1m'],
    QrLoginStatusController::class,
]);

$router->post('/auth/qr-login/scan', [
    UserApiTokenAuthMiddleware::class,
    RateLimitMiddleware::class => ['limit' => '30/1m'],
    QrLoginScanController::class,
]);

$router->post('/auth/qr-login/confirm', [
    UserApiTokenAuthMiddleware::class,
    RateLimitMiddleware::class => ['limit' => '30/1m'],
    QrLoginConfirmController::class,
]);

// Current user
$router->post('/me', [
    UserApiTokenAuthMiddleware::class,
    [ProfileController::class, 'get'],
]);

// User agreement endpoints
$router->post('/agreements/current', [
    RateLimitMiddleware::class => ['limit' => '60/1m'],
    [UserAgreementController::class, 'current'],
]);

$router->post('/user-agreements/current', [
    RateLimitMiddleware::class => ['limit' => '60/1m'],
    [UserAgreementController::class, 'current'],
]);

$router->post('/agreements/history', [
    RateLimitMiddleware::class => ['limit' => '60/1m'],
    [UserAgreementController::class, 'history'],
]);

$router->post('/user-agreements/history', [
    RateLimitMiddleware::class => ['limit' => '60/1m'],
    [UserAgreementController::class, 'history'],
]);

$router->post('/agreements/detail', [
    RateLimitMiddleware::class => ['limit' => '60/1m'],
    [UserAgreementController::class, 'detail'],
]);

$router->post('/user-agreements/detail', [
    RateLimitMiddleware::class => ['limit' => '60/1m'],
    [UserAgreementController::class, 'detail'],
]);

// Transfer endpoints
$router->post('/transfer/convert', [
    UserApiTokenAuthMiddleware::class,
    RateLimitMiddleware::class => ['limit' => '20/1m'],
    TransferConvertController::class,
]);

$router->post('/transfer/short-link', [
    UserApiTokenAuthMiddleware::class,
    RateLimitMiddleware::class => ['limit' => '20/1m'],
    TransferShortLinkController::class,
]);

// ================================
// Development Only
// ================================

if (App::config('app.env') === 'local') {
    // OpenAPI schema for API documentation
    $router->post('/openapi.json', [
        [OpenApiController::class, 'get'],
    ]);

    $router->post('/stream', [
        RateLimitMiddleware::class => ['limit' => '10/1m'],
        [StreamController::class, 'get'],
    ]);
}
