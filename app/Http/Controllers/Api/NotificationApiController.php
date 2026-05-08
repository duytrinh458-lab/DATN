<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationApiController extends Controller
{
    // 📌 API 54: Lấy danh sách thông báo của user (GET /api/notifications)
    public function index()
    {
        $userId = Auth::id();

        // Lấy thông báo từ bảng notifications thật trong CSDL
        $notifications = DB::table('notifications')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách thông báo thành công',
            'data' => $notifications
        ]);
    }

    // 📌 API 55: Đánh dấu thông báo đã đọc (POST /api/notifications/read)
    public function markAsRead(Request $request)
    {
        $request->validate([
            'noti_id' => 'required|exists:notifications,id'
        ]);

        $userId = Auth::id();

        // Cập nhật trạng thái is_read = 1
        DB::table('notifications')
            ->where('id', $request->noti_id)
            ->where('user_id', $userId)
            ->update(['is_read' => 1]);

        return response()->json([
            'status' => true,
            'message' => 'Đã đánh dấu thông báo đã đọc'
        ]);
    }
}