<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\PostLikeController;
use App\Http\Controllers\Api\PostCommentController;
use App\Http\Controllers\Api\PostRepostController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserFollowListController;
use App\Http\Controllers\Api\CommentLikeController;
use App\Http\Controllers\Api\CommentBookmarkController;
use App\Http\Controllers\Api\CommentRepostController;


// 動作確認用（開発中は残す）
Route::get('/ping', function () {
    return ['message' => 'pong'];
});
// 登録
Route::post('register', [AuthController::class, 'register']);

// ログイン
Route::post('/login', [AuthController::class, 'login']);

// ログイン中ユーザー（Sanctum 認証必須）
Route::middleware('auth:sanctum')->get('/me', function () {
    return auth()->user(); //動作上問題ないエラー
});

Route::middleware('auth:sanctum')->get('/protected', function (Request $request) {
    return response()->json([
        'message' => 'You are authenticated',
        'user' => $request->user(),
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    // Post index
    Route::get('/posts', [PostController::class, 'index']);
    // Post store
    Route::post('/posts', [PostController::class, 'store']);
    // Post show
    Route::get('/posts/{post}', [PostController::class, 'show']);
    // Post update
    Route::put('/posts/{post}', [PostController::class, 'update']);
    // Post destroy
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);

    // Bookmark index
    Route::get('/bookmarks', [BookmarkController::class, 'index']);
    // Bookmark store
    Route::post('/posts/{post}/bookmark', [BookmarkController::class, 'store']);
    // Bookmark destroy
    Route::delete('/posts/{post}/bookmark', [BookmarkController::class, 'destroy']); 

    // Like store
    Route::post('/posts/{post}/like', [PostLikeController::class, 'store']);
    // Like destroy
    Route::delete('/posts/{post}/like', [PostLikeController::class, 'destroy']);

    // Comment index
    Route::get('/posts/{post}/comments', [PostCommentController::class, 'index']);
    // Comment store
    Route::post('/posts/{post}/comments', [PostCommentController::class, 'store']);
    // Comment update
    Route::patch('/comments/{comment}', [PostCommentController::class, 'update']);
    // Comment destroy
    Route::delete('/comments/{comment}', [PostCommentController::class, 'destroy']);

    // repost store
    Route::post('/posts/{post}/repost', [PostRepostController::class, 'store']);
    // repost destroy
    Route::delete('/posts/{post}/repost', [PostRepostController::class, 'destroy']);

    // follow store
    Route::post('/users/{user}/follow', [FollowController::class, 'store']);
    // follow destroy
    Route::delete('/users/{user}/follow', [FollowController::class, 'destroy']);
    // user info
    Route::get('/users/{user}', [UserController::class, 'show']);
    // following
    Route::get('/users/{user}/following', [UserFollowListController::class, 'following']);
    // follower
    Route::get('/users/{user}/followers', [UserFollowListController::class, 'followers']);

    // comment-like store
    Route::post('/comments/{comment}/like', [CommentLikeController::class, 'store']);
    // comment-like destroy
    Route::delete('/comments/{comment}/like', [CommentLikeController::class, 'destroy']);

    // comment-repost store
    Route::post('/comments/{comment}/repost', [CommentRepostController::class, 'store']);
    // comment-repost destroy
    Route::delete('/comments/{comment}/repost', [CommentRepostController::class, 'destroy']);

    // comment-bookmark store
    Route::post('/comments/{comment}/bookmark', [CommentBookmarkController::class, 'store']);
    // comment-bookmark destroy
    Route::delete('/comments/{comment}/bookmark', [CommentBookmarkController::class, 'destroy']);
});

// logout
Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();

    return response()->json(['message' => 'Logged out']);
});