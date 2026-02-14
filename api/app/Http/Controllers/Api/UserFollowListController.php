<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserFollowListController extends Controller
{
  public function following(Request $request, User $user)
  {
    $me = $request->user();

    $users = $user->following()
      ->select('users.id', 'users.name')
      ->withCount(['followers', 'following'])
      ->orderBy('users.name')
      ->get();

    // ログイン中の私が「その人」をフォローしてるか（ボタン用）
    $myFollowingIds = $me->following()->pluck('users.id')->all();

    $data = $users->map(fn($u) => [
      'id' => $u->id,
      'name' => $u->name,
      'bio' => $u->bio ?? null, // bioカラムまだなら null でOK
      'is_following' => in_array($u->id, $myFollowingIds, true),
      'followers_count' => $u->followers_count,
      'following_count' => $u->following_count,
    ]);

    return response()->json(['data' => $data]);
  }

  public function followers(Request $request, User $user)
  {
    $me = $request->user();

    $users = $user->followers()
      ->select('users.id', 'users.name')
      ->withCount(['followers', 'following'])
      ->orderBy('users.name')
      ->get();

    $myFollowingIds = $me->following()->pluck('users.id')->all();

    $data = $users->map(fn($u) => [
      'id' => $u->id,
      'name' => $u->name,
      'bio' => $u->bio ?? null,
      'is_following' => in_array($u->id, $myFollowingIds, true),
      'followers_count' => $u->followers_count,
      'following_count' => $u->following_count,
    ]);

    return response()->json(['data' => $data]);
  }
}
