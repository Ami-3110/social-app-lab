<?php
// app/Http/Controllers/Api/FollowController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
  public function store(Request $request, User $user)
  {
    $me = $request->user();
    abort_if($me->id === $user->id, 422);

    $me->following()->syncWithoutDetaching([$user->id]);

    return response()->json(['following' => true]);
  }

  public function destroy(Request $request, User $user)
  {
    $me = $request->user();
    $me->following()->detach($user->id);

    return response()->json(['following' => false]);
  }
}
