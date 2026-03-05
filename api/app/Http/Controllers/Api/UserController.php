<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class UserController extends Controller
{
  public function show(Request $request, User $user)
  {
      $me = $request->user();

      $user->loadCount(['following', 'followers']);

      $isFollowing = $me
          ? $me->following()->whereKey($user->id)->exists()
          : false;

      return response()->json([
          'data' => [
              'id' => $user->id,
              'name' => $user->name,
              'email'=> $me && $me->id === $user->id ? $user->email : null,

              'is_following' => $isFollowing,
              'following_count' => $user->following_count,
              'followers_count' => $user->followers_count,
          ],
      ]);
  }

  public function comments(User $user)
  {
    $meId = Auth::id();

    $comments = Comment::query()
      ->with([
        'user:id,name',

        // 親post（PostCardに必要な情報）
        'post' => function ($q) use ($meId) {
          $q->with(['user:id,name'])
            ->withCount(['likes', 'comments', 'reposts'])
            ->withExists([
              'likes as is_liked' => fn($qq) => $qq->where('user_id', $meId),
              'reposts as is_reposted' => fn($qq) => $qq->where('user_id', $meId),
              'bookmarkedBy as is_bookmarked' => fn($qq) => $qq->where('user_id', $meId),
            ]);
        },
      ])
      ->where('user_id', $user->id)
      ->latest()
      ->paginate(10);

    return response()->json($comments);
  }

  public function likedPosts(User $user)
  {
    $meId = Auth::id();

    $posts = Post::query()
      ->with(['user:id,name'])

      // counts（ActionBar用）
      ->withCount(['likes', 'comments', 'reposts'])

      // flags（自分がやってるか）
      ->withExists([
        'likes as is_liked' => fn($q) => $q->where('user_id', $meId),
        'reposts as is_reposted' => fn($q) => $q->where('user_id', $meId),
      'bookmarkedBy as is_bookmarked' => fn($q) => $q->where('user_id', $meId),
      ])

      // liked posts の抽出条件（閲覧対象 user がいいねしたpost）
      ->whereHas('likes', fn($q) => $q->where('user_id', $user->id))

      ->latest()
      ->paginate(10);

    return response()->json($posts);
  }
}
