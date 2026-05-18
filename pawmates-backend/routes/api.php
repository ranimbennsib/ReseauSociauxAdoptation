<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\AdoptionRequestController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// ─── Auth (Public) ────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
});

// ─── Posts (Public) ───────────────────────────────────────────────
// ✅ Toutes les routes fixes AVANT la route dynamique {id}
Route::get('/posts',                     [PostController::class, 'index']);
Route::get('/posts/adoption',            [PostController::class, 'adoption']);
Route::get('/posts/donation',            [PostController::class, 'donation']);
Route::get('/posts/category/{category}', [PostController::class, 'byCategory']);
Route::get('/posts/city/{city}',         [PostController::class, 'byCity']);
Route::get('/posts/search',              [PostController::class, 'search']);

// ─── Comments (Public) ────────────────────────────────────────────
Route::get('/comments/post/{postId}',       [CommentController::class, 'byPost']);
Route::get('/comments/post/{postId}/count', [CommentController::class, 'count']);

// ─── Donations (Partial public) ───────────────────────────────────
Route::get('/donations/post/{postId}/count', [DonationController::class, 'count']);

// ─── Auth Required ────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me',      [AuthController::class, 'me']);

    // Posts (authenticated)
    // ✅ /posts/my-posts est ici, ET /posts/{id} est déclaré APRÈS ce groupe
    // → Laravel ne confondra plus "my-posts" avec un {id}
    Route::get('/posts/my-posts',       [PostController::class, 'myPosts']);
    Route::post('/posts',               [PostController::class, 'store']);
    Route::put('/posts/{id}',           [PostController::class, 'update']);
    Route::patch('/posts/{id}/close',   [PostController::class, 'close']);

    // Adoption Requests
    Route::post('/adoption_requests',                  [AdoptionRequestController::class, 'store']);
    Route::get('/adoption_requests/sent',              [AdoptionRequestController::class, 'sent']);
    Route::get('/adoption_requests/received',          [AdoptionRequestController::class, 'received']);
    Route::get('/adoption_requests/post/{postId}',     [AdoptionRequestController::class, 'byPost']);
    Route::patch('/adoption_requests/{id}/accept',     [AdoptionRequestController::class, 'accept']);
    Route::patch('/adoption_requests/{id}/reject',     [AdoptionRequestController::class, 'reject']);
    Route::patch('/adoption_requests/{id}/cancel',     [AdoptionRequestController::class, 'cancel']);

    // Comments (authenticated)
    Route::post('/comments',            [CommentController::class, 'store']);
    Route::get('/comments/my-comments', [CommentController::class, 'myComments']);
    Route::patch('/comments/{id}',      [CommentController::class, 'update']);
    Route::delete('/comments/{id}',     [CommentController::class, 'destroy']);

    // Donations (authenticated)
    Route::post('/donations',                    [DonationController::class, 'store']);
    Route::get('/donations/my-donations',        [DonationController::class, 'myDonations']);
    Route::get('/donations/received',            [DonationController::class, 'received']);
    Route::get('/donations/post/{postId}',       [DonationController::class, 'byPost']);
    Route::get('/donations/post/{postId}/total', [DonationController::class, 'total']);

    // Reports
    Route::post('/reports', [ReportController::class, 'store']);
});

// ✅ /posts/{id} déclaré EN DERNIER — après toutes les routes fixes
// Laravel ne pourra plus capturer "my-posts" ici car ce segment
// aura déjà été résolu par la route au-dessus
Route::get('/posts/{id}', [PostController::class, 'show']);

// ─── Admin Only ───────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    // Posts admin
    Route::patch('/posts/{id}/remove', [PostController::class, 'remove']);

    // Reports
    Route::get('/reports',                     [ReportController::class, 'index']);
    Route::get('/reports/status/{status}',     [ReportController::class, 'byStatus']);
    Route::get('/reports/post/{postId}',       [ReportController::class, 'byPost']);
    Route::get('/reports/post/{postId}/count', [ReportController::class, 'countByPost']);
    Route::patch('/reports/{id}/review',       [ReportController::class, 'review']);
    Route::patch('/reports/{id}/resolve',      [ReportController::class, 'resolve']);
    Route::patch('/reports/{id}/dismiss',      [ReportController::class, 'dismiss']);

    // Admin panel
    Route::get('/admin/users',                 [AdminController::class, 'users']);
    Route::patch('/admin/users/{id}/promote',  [AdminController::class, 'promote']);
    Route::patch('/admin/users/{id}/demote',   [AdminController::class, 'demote']);
    Route::get('/admin/posts',                 [AdminController::class, 'posts']);
    Route::get('/admin/posts/status/{status}', [AdminController::class, 'postsByStatus']);
    Route::get('/admin/statistics',            [AdminController::class, 'statistics']);
});