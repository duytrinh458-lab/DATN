<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    public function index()
    {
        $notifications = [
            [
                'id' => 1,
                'title' => 'Đơn hàng đã được xác nhận',
                'message' => 'Đơn hàng #1001 của bạn đã được xác nhận',
                'is_read' => false,
                'created_at' => now()->format('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'title' => 'Khuyến mãi mới',
                'message' => 'Giảm giá 20% cho tất cả sản phẩm',
                'is_read' => true,
                'created_at' => now()->subHours(2)->format('Y-m-d H:i:s')
            ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách thông báo thành công',
            'data' => $notifications
        ]);
    }
}