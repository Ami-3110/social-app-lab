<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Comment;
use App\Observers\CommentObserver;
use App\Models\Like;
use App\Observers\LikeObserver;
use App\Models\CommentLike;
use App\Observers\CommentLikeObserver;
use App\Models\Post;
use App\Observers\PostObserver;
use App\Models\Bookmark;
use App\Observers\BookmarkObserver;
use App\Models\CommentBookmark;
use App\Observers\CommentBookmarkObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Comment::observe(CommentObserver::class);
        Like::observe(LikeObserver::class);
        CommentLike::observe(CommentLikeObserver::class);
        Post::observe(PostObserver::class);
        Bookmark::observe(BookmarkObserver::class);
        CommentBookmark::observe(CommentBookmarkObserver::class);
    }
}
