<?php
// app/Http/Controllers/Api/PostRepostController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostRepostController extends Controller
{
  public function store(Request $request, Post $post)
  {
    $request->validate([
      'quote_body' => ['nullable', 'string', 'max:1000'],
    ]);

    $quote = trim((string) $request->input('quote_body'));

    Post::create([
      'user_id' => $request->user()->id,
      'title' => $post->title,
      'body' => $post->body,
      'topic' => $post->topic,
      'repost_of_post_id' => $post->id,
      'quote_body' => $quote !== '' ? $quote : null,
    ]);

    return response()->noContent();
  }


  public function destroy(Request $request, Post $post)
  {
    $userId = $request->user()->id;

    Post::query()
      ->where('user_id', $userId)
      ->where('repost_of_post_id', $post->id)
      ->latest('id')
      ->first()
      ?->delete();

    return response()->noContent();
  }
}
