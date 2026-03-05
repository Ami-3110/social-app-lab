<?php
// app/Http/Controllers/Api/MeController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MeController extends Controller
{
  public function show(Request $request)
  {
    return response()->json(['data' => $request->user()]);
  }

  public function updateProfile(Request $request)
  {
    $data = $request->validate([
      'bio' => ['nullable', 'string', 'max:1000'],
    ]);

    $me = $request->user();
    $me->bio = $data['bio'] ?? null;
    $me->save();

    return response()->json(['data' => $me]);
  }

  public function uploadAvatar(Request $request)
  {
    $request->validate([
      'avatar' => ['required', 'file', 'image', 'max:5120'], // 5MB
    ]);

    $me = $request->user();

    // 今は削除不要だけど、将来のために “上書き時だけ” 消しておくと綺麗
    // if ($me->avatar_path) Storage::disk('public')->delete($me->avatar_path);
    // if ($me->avatar_original_path) Storage::disk('public')->delete($me->avatar_original_path);

    $file = $request->file('avatar');

    // 将来トリミング入れるので、元画像は original に保存
    $originalPath = $file->store('avatars/original', 'public');

    // 今はトリミング無し：表示用も同じファイルを使う（将来ここを cropped に差し替える）
    $me->avatar_original_path = $originalPath;
    $me->avatar_path = $originalPath;
    $me->save();

    return response()->json(['data' => $me]);
  }
}