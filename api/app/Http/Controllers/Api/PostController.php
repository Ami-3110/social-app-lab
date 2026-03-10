<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function index(Request $request)
    {
      $me = $request->user();

      if (!$me) {
        return response()->json(['message' => 'Unauthenticated.'], 401);
      }

      $topic = $request->query('topic');
      $tab = $request->query('tab', 'All');
      $userId = $request->query('user_id');
      $q = trim((string) $request->query('q', ''));

      $meId = $me->id;

      $query = Post::query()
        ->latest()
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
        ])

        // Following
        ->when($tab === 'Following', function ($query) use ($me) {
          $followingIds = $me->following()->select('users.id');
          $query->whereIn('user_id', $followingIds);
        })

        // Profile (User filter)
        ->when($request->filled('user_id'), function ($query) use ($userId) {
          $query->where('user_id', (int) $userId);
        })

        // topic
        ->when($topic, function ($query) use ($topic) {
          $t = trim((string) $topic);

          if ($t === '' || $t === '__none__') {
            $query->whereRaw('1 = 0');
            return;
          }

          $query->where('topic', 'like', '%' . $t . '%');
        })

        // keyword search
        ->when($q !== '', function ($query) use ($q) {
          $query->where(function ($sub) use ($q) {
            $sub->where('body', 'like', '%' . $q . '%')
              ->orWhere('topic', 'like', '%' . $q . '%');
          });
        });

      return $query->paginate(10)->withQueryString();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body'  => ['required', 'string'],
            'topic' => ['nullable', 'string', 'max:100'],
            'media' => ['nullable', 'array', 'max:4'],
            'media.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $post = DB::transaction(function () use ($request, $data){
          $post =Post::create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'topic' => $data['topic'] ?? null,
          ]);

          if ($request->hasFile('media')) {
            foreach($request->file('media') as $index => $file){
              $path = $file->store('posts', 'public');

              $post->media()->create([ 
                'path' => $path,
                'sort_order' => $index,
              ]);
            }
          }
          return $post;
        });

        return response()->json($post->load('media'), 201);
    }

    public function show(Request $request, Post $post)
    {
      $me = $request->user();

        $post->load([
          'user:id,name,avatar_path',
          'media',
          'originalPost.user:id,name,avatar_path',
          'originalPost.media',
          'repostOfComment.user:id,name,avatar_path',
          'repostOfComment.post.media',
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

        return $post->load('user:id,name,avatar_path');
    }

    public function destroy(Request $request, Post $post)
    {
        // まずは簡易：自分の投稿だけ削除できる
        abort_if($post->user_id !== $request->user()->id, 403);

        $post->delete();

        return response()->json(['message' => 'Post deleted']);
    }

    public function searchTopics(Request $request)
    {
      $me = $request->user();

      if (!$me) {
        return response()->json(['message' => 'Unauthenticated.'], 401);
      }

      $q = trim((string) $request->query('q', ''));

      $query = Post::query()
        ->select('topic')
        ->whereNotNull('topic')
        ->where('topic', '!=', '');

      if ($q !== '') {
        $query->where('topic', 'like', '%' . $q . '%');
      }

      $topics = $query
        ->distinct()
        ->orderBy('topic')
        ->paginate(10)
        ->through(fn($post) => [
          'topic' => $post->topic,
        ])
        ->withQueryString();

      return response()->json($topics);
    }
}



