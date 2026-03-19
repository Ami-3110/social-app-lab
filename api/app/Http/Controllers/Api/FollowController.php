<?php
// app/Http/Controllers/Api/FollowController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;

class FollowController extends Controller
{
  public function store(Request $request, User $user)
  {
    $me = $request->user();
    abort_if($me->id === $user->id, 422);

    $alreadyFollowing = $me->following()->whereKey($user->id)->exists();

    $me->following()->syncWithoutDetaching([$user->id]);

    if (! $alreadyFollowing) {
      Notification::create([
        'user_id' => $user->id,
        'actor_id' => $me->id,
        'type' => 'follow',
        'post_id' => null,
        'comment_id' => null,
      ]);
    }

    return response()->json(['following' => true]);
  }

  public function destroy(Request $request, User $user)
  {
    $me = $request->user();
    $me->following()->detach($user->id);

    return response()->json(['following' => false]);
  }
}
