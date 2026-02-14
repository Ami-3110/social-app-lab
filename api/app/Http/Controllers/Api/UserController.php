<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
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
}
