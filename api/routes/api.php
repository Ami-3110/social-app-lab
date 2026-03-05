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

    //------- Action to POST -------
    // Post bookmark index
    Route::get('/bookmarks', [BookmarkController::class, 'index']);
    // Post bookmark store
    Route::post('/posts/{post}/bookmark', [BookmarkController::class, 'store']);
    // Post bookmark destroy
    Route::delete('/posts/{post}/bookmark', [BookmarkController::class, 'destroy']);

    // Post like store
    Route::post('/posts/{post}/like', [PostLikeController::class, 'store']);
    // Post like destroy
    Route::delete('/posts/{post}/like', [PostLikeController::class, 'destroy']);

    // Post comment index
    Route::get('/posts/{post}/comments', [PostCommentController::class, 'index']);
    // Post comment store
    Route::post('/posts/{post}/comments', [PostCommentController::class, 'store']);
    // Post comment update
    Route::patch('/comments/{comment}', [PostCommentController::class, 'update']);
    // Post comment destroy
    Route::delete('/comments/{comment}', [PostCommentController::class, 'destroy']);

    // Post repost store
    Route::post('/posts/{post}/repost', [PostRepostController::class, 'store']);
    // Post repost destroy
    Route::delete('/posts/{post}/repost', [PostRepostController::class, 'destroy']);

  //------- My page -------
   // Follow store
   Route::post('/users/{user}/follow', [FollowController::class, 'store']);
    // Follow destroy
    Route::delete('/users/{user}/follow', [FollowController::class, 'destroy']);
    // Mypage Top(defalt: post tab)
    Route::get('/users/{user}', [UserController::class, 'show']);
    // Following
    Route::get('/users/{user}/following', [UserFollowListController::class, 'following']);
    // Follower
    Route::get('/users/{user}/followers', [UserFollowListController::class, 'followers']);
    // Mypage comment tab
    Route::get('/users/{user}/comments', [UserController::class, 'comments']);
  // Mypage media tab

    // Mypage liked tab
    Route::get('/users/{user}/liked-posts', [UserController::class, 'likedPosts']);

    //------- Action to COMMENT -------
    // Comment like store
    Route::post('/comments/{comment}/like', [CommentLikeController::class, 'store']);
    // Comment like destroy
    Route::delete('/comments/{comment}/like', [CommentLikeController::class, 'destroy']);

    // Comment comment route is the same as Post comment

    // Comment repost store
    Route::post('/comments/{comment}/repost', [CommentRepostController::class, 'store']);
    // Comment repost destroy
    Route::delete('/comments/{comment}/repost', [CommentRepostController::class, 'destroy']);

    // Comment bookmark store
    Route::post('/comments/{comment}/bookmark', [CommentBookmarkController::class, 'store']);
    // Comment bookmark destroy
    Route::delete('/comments/{comment}/bookmark', [CommentBookmarkController::class, 'destroy']);

});

// logout
Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();

    return response()->json(['message' => 'Logged out']);
});