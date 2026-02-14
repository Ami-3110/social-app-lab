<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {      
      $topic = $request->query('topic');
      $tab = $request->query('tab', 'All');
      $me = $request->user();

      $query = Post::query()
        ->latest()
        ->with([
          'user:id,name',
          'originalPost.user:id,name'
        ])
        ->withCount([
          'likes',
          'comments',
          'reposts as reposts_count',

          'likedUsers as is_liked' =>
          fn($q) => $q->where('user_id', $me->id),

          'bookmarkedBy as is_bookmarked' =>
          fn($q) => $q->where('user_id', $me->id),

          'reposts as is_reposted' =>
          fn($q) => $q->where('user_id', $me->id),
        ])

        // ✅ Following
        ->when($tab === 'Following', function ($q) use ($me) {
          $followingIds = $me->following()->select('users.id');

          $q->whereIn('user_id', $followingIds);
        })

        ->when($topic, function ($q) use ($topic) {
          $t = trim((string) $topic);
          if ($t === '' || $t === '__none__') {
            $q->whereRaw('1 = 0');
            return;
          }
          $q->where('topic', 'like', '%' . $t . '%');
        });

      return $query->paginate(10)->withQueryString();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body'  => ['required', 'string'],
            'topic' => ['nullable', 'string', 'max:100'],
        ]);

        $post = Post::create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'topic' => $data['topic'] ?? null,
        ]);

        return response()->json($post, 201);
    }

    public function show(Request $request, Post $post)
    {
      $me = $request->user();

        $post->load([
          'user:id,name',
          'originalPost.user:id,name',
        ]);
        $post->loadCount([
          'likes',
          'comments',
          'reposts as reposts_count',
          'likedUsers as is_liked' => fn($q) => $q->where('users.id', $request->user()->id),
          'bookmarkedBy as is_bookmarked' => fn($q) => $q->where('users.id', $request->user()->id),
          'reposts as is_reposted' => fn($q) => $q->where('user_id', $request->user()->id),
        ]);
      return response()->json(['data' => $post]);
    }

    public function update(Request $request, Post $post)
    {
        // まずは簡易：自分の投稿だけ更新できる
        abort_if($post->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body'  => ['required', 'string'],
            'topic' => ['nullable', 'string', 'max:100'],
        ]);

        $post->update([
            'title' => $data['title'],
            'body' => $data['body'],
            'topic' => $data['topic'] ?? null,
        ]);

        return $post->load('user:id,name');
    }

    public function destroy(Request $request, Post $post)
    {
        // まずは簡易：自分の投稿だけ削除できる
        abort_if($post->user_id !== $request->user()->id, 403);

        $post->delete();

        return response()->json(['message' => 'Post deleted']);
    }
}



