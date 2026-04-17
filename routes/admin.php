<?php

use BaseApi\App;
use App\Middleware\CombinedAuthMiddleware;
use BaseApi\Http\SessionStartMiddleware;
use BaseApi\Http\Middleware\RateLimitMiddleware;
use App\Controllers\Admin\UserAgreementController;
use App\Controllers\Admin\DashboardController;

$router = App::router();

$router->post('/admin/dashboard', [
    SessionStartMiddleware::class,
    CombinedAuthMiddleware::class,
    RateLimitMiddleware::class => ['limit' => '30/1m'],
    [DashboardController::class, 'get'],
]);

$router->post('/admin/agreements', [
    SessionStartMiddleware::class,
    CombinedAuthMiddleware::class,
    RateLimitMiddleware::class => ['limit' => '60/1m'],
    [UserAgreementController::class, 'index'],
]);

$router->post('/admin/user-agreements', [
    SessionStartMiddleware::class,
    CombinedAuthMiddleware::class,
    RateLimitMiddleware::class => ['limit' => '60/1m'],
    [UserAgreementController::class, 'index'],
]);

$router->post('/admin/agreements/detail', [
    SessionStartMiddleware::class,
    CombinedAuthMiddleware::class,
    RateLimitMiddleware::class => ['limit' => '60/1m'],
    [UserAgreementController::class, 'detail'],
]);

$router->post('/admin/user-agreements/detail', [
    SessionStartMiddleware::class,
    CombinedAuthMiddleware::class,
    RateLimitMiddleware::class => ['limit' => '60/1m'],
    [UserAgreementController::class, 'detail'],
]);

$router->post('/admin/agreements/save', [
    SessionStartMiddleware::class,
    CombinedAuthMiddleware::class,
    RateLimitMiddleware::class => ['limit' => '20/1m'],
    [UserAgreementController::class, 'save'],
]);

$router->post('/admin/user-agreements/save', [
    SessionStartMiddleware::class,
    CombinedAuthMiddleware::class,
    RateLimitMiddleware::class => ['limit' => '20/1m'],
    [UserAgreementController::class, 'save'],
]);

$router->post('/admin/agreements/activate', [
    SessionStartMiddleware::class,
    CombinedAuthMiddleware::class,
    RateLimitMiddleware::class => ['limit' => '20/1m'],
    [UserAgreementController::class, 'activate'],
]);

$router->post('/admin/user-agreements/activate', [
    SessionStartMiddleware::class,
    CombinedAuthMiddleware::class,
    RateLimitMiddleware::class => ['limit' => '20/1m'],
    [UserAgreementController::class, 'activate'],
]);
