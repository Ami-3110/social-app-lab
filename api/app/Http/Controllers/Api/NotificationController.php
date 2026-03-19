<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
    

}
