<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'username'   => $this->username,
            'full_name'  => $this->full_name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'avatar'     => $this->avatar,
            'status'     => $this->status,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            
            // 🔒 BẢO MẬT: Chỉ trả về vai trò và trạng thái hệ thống khi người gọi yêu cầu 
            // là chính chủ sở hữu tài khoản này HOẶC người gọi là tài khoản Admin.
            'role' => $this->when(
                Auth::check() && (Auth::user()->role === 'admin' || Auth::id() === $this->id),
                $this->role
            ),
            'is_verified' => $this->when(
                Auth::check() && (Auth::user()->role === 'admin' || Auth::id() === $this->id),
                (bool) $this->is_verified
            ),
            'is_first_login' => $this->when(
                Auth::check() && (Auth::user()->role === 'admin' || Auth::id() === $this->id),
                (bool) $this->is_first_login
            ),
        ];
    }
}