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
              'bio' => $user->bio,
              'avatar_path' => $user->avatar_path,
              'avatar_url' => $user->avatar_url,
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
        'user:id,name,avatar_path',

        // 親post（PostCardに必要な情報）
        'post' => function ($q) use ($meId) {
          $q->with(['user:id,name,avatar_path'])
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

  public function mediaPosts(Request $request, User $user)
  {
    $me = $request->user();

    if (!$me) {
      return response()->json(['message' => 'Unauthenticated.'], 401);
    }

    $meId = $me->id;

    $query = Post::query()
      ->latest()
      ->where('user_id', $user->id)
      ->whereHas('media')
      ->with([
        'user:id,name,avatar_path',
        'media',
        'originalPost.user:id,name,avatar_path',
        'originalPost.media',
        'repostOfComment.user:id,name,avatar_path',
        'repostOfComment.post.user:id,name,avatar_path',
        'repostOfComment.post.media',
      ])
      ->withCount([
        'likes',
        'comments',
        'reposts as reposts_count',

        'likedUsers as is_liked' =>
        fn($q2) => $q2->where('user_id', $meId),

        'bookmarkedBy as is_bookmarked' =>
        fn($q2) => $q2->where('user_id', $meId),

        'reposts as is_reposted' =>
        fn($q2) => $q2->where('user_id', $meId),
      ]);

    return $query->paginate(5)->withQueryString();
  }

  public function likedPosts(User $user)
  {
    $meId = Auth::id();

    $posts = Post::query()
      ->with(['user:id,name,avatar_path'])

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

  public function search(Request $request)
  {
    $me = $request->user();

    if (!$me) {
      return response()->json(['message' => 'Unauthenticated.'], 401);
    }

    $q = trim((string) $request->query('q', ''));

    $query = User::query()
      ->select('id', 'name', 'bio', 'avatar_path')
      ->withCount([
        'following',
        'followers',
        'followers as is_following' => fn($sub) => $sub->whereKey($me->id),
      ])
      ->latest();

    if ($q !== '') {
      $query->where(function ($sub) use ($q) {
        $sub->where('name', 'like', '%' . $q . '%')
          ->orWhere('bio', 'like', '%' . $q . '%');
      });
    }

    return $query->paginate(10)->withQueryString();
  }
}
