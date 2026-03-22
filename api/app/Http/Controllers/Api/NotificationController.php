<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notification;


class NotificationController extends Controller
{
  public function index(Request $request)
  {
    $notifications = $request->user()
      ->notifications()
      ->with(['actor', 'post', 'comment'])
      ->paginate(20);

    return response()->json($notifications);
  }

  public function unreadCount(Request $request)
  {
    $count = Notification::query()
      ->where('user_id', $request->user()->id)
      ->whereNull('read_at')
      ->count();

    return response()->json([
      'count' => $count,
    ]);
  }

  public function readAll(Request $request)
  {
    Notification::query()
      ->where('user_id', $request->user()->id)
      ->whereNull('read_at')
      ->update([
        'read_at' => now(),
      ]);

    return response()->json([
      'message' => 'Notifications marked as read.',
    ]);
  }

}
