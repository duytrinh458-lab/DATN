<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * MỤC ĐÍCH FILE:
 * File này quản lý toàn bộ các tính năng liên quan đến xác thực tài khoản và bảo mật của người dùng trong hệ thống.
 * Bao gồm các luồng: Đăng ký tài khoản mới, Đăng nhập, Đăng xuất, Quản lý gửi/xác thực mã OTP, và Đổi/Khôi phục mật khẩu.
 */

/**
 * CHỨC NĂNG CLASS:
 * Tiếp nhận dữ liệu từ các yêu cầu (Request) của client, tiến hành kiểm tra tính hợp lệ (Validate),
 * tương tác với cơ sở dữ liệu (bảng 'users' và 'otp_verifications') để xử lý logic nghiệp vụ và trả về kết quả dạng JSON.
 */
class AuthApiController extends Controller
{
    // 📌 1. ĐĂNG KÝ (SIGNUP)
    // VAI TRÒ: Tiếp nhận thông tin đăng ký của khách hàng mới, tạo tài khoản tạm thời và phát hành mã OTP để xác thực số điện thoại.
    public function signup(Request $request)
    {
        // KHỐI LỆNH: Kiểm tra tính hợp lệ của dữ liệu đầu vào. 
        // Bắt buộc phải có đầy đủ thông tin; Email và Số điện thoại không được phép trùng lặp với tài khoản khác trong cơ sở dữ liệu.
        $request->validate([
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:6',
            'full_name' => 'required',
            'phone'     => 'required|unique:users'
        ]);

        // BIẾN QUAN TRỌNG: Tạo tên đăng nhập tự động bằng cách lấy phần chữ trước ký tự '@' của email kết hợp với một số ngẫu nhiên từ 100 đến 999 để tránh trùng nhau.
        $username = explode('@', $request->email)[0] . rand(100, 999);

        // TRUY VẤN: Tạo và lưu một bản ghi người dùng mới vào bảng 'users'. 
        // Tài khoản mặc định có quyền là 'customer', mật khẩu được mã hóa an toàn qua thư viện Hash, và trạng thái xác thực ban đầu là chưa xác thực (is_verified = 0).
        $user = User::create([
            'username'    => $username,
            'full_name'   => $request->full_name,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'role'        => 'customer',
            'is_verified' => 0,
            'password'    => Hash::make($request->password),
        ]);

        // KHỐI LỆNH: Tạo mã OTP gồm 6 chữ số ngẫu nhiên.
        // Ý tưởng thuật toán: Sử dụng `random_int` để tạo số ngẫu nhiên có độ bảo mật cao về mặt mật mã học.
        // Nếu hệ điều hành gặp lỗi hiếm hoi không cung cấp đủ tài nguyên bảo mật (Entropy), khối catch sẽ kích hoạt và dùng hàm dự phòng `mt_rand` để ứng dụng không bị sập.
        try {
            $otp = random_int(100000, 999999);
        } catch (\Exception $e) {
            $otp = mt_rand(100000, 999999); 
        }

        // TRUY VẤN: Lưu thông tin mã OTP vừa tạo vào bảng 'otp_verifications' với loại giao dịch là 'register' (đăng ký) và đặt thời gian hết hạn là 5 phút.
        DB::table('otp_verifications')->insert([
            'phone'      => $request->phone,
            'otp_code'   => $otp,
            'type'       => 'register',
            'expires_at' => now()->addMinutes(5),
            'created_at' => now()
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Đăng ký thành công! Mã OTP đã được gửi.',
            'data'    => [
                // XỬ LÝ: Tạo chuỗi Token truy cập qua Laravel Sanctum để người dùng dùng cho các API cần bảo mật tiếp theo.
                'token' => $user->createToken('VanguardToken')->plainTextToken
            ]
        ], 201);
    }

    // 📌 2. ĐĂNG NHẬP (LOGIN)
    // VAI TRÒ: Kiểm tra thông tin tài khoản, mật khẩu và trạng thái của người dùng để cấp quyền truy cập hệ thống.
    public function login(Request $request)
    {
        // KHỐI LỆNH: Bắt buộc điền đúng định dạng Email và nhập Mật khẩu.
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        // TRUY VẤN: Tìm kiếm xem có người dùng nào sở hữu email này trong hệ thống hay không.
        $user = User::where('email', $request->email)->first();

        // KHỐI LỆNH: Kiểm tra tài khoản và mật khẩu.
        // Nếu không tìm thấy người dùng HOẶC mật khẩu nhập vào sau khi hash không khớp với mật khẩu trong DB thì báo lỗi 401 (Sai thông tin đăng nhập).
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Tài khoản hoặc mật khẩu không chính xác'
            ], 401);
        }

        // 🔥 ĐÃ VÁ BUG #7: Chặn đứng tài khoản bị khóa hoặc vô hiệu hóa
        // KHỐI LỆNH: Nếu trạng thái tài khoản khác 'active' (ví dụ: bị ban hoặc tạm khóa), lập tức từ chối cho đăng nhập.
        if ($user->status !== 'active') {
            return response()->json([
                'status'  => false, 
                'message' => 'Tài khoản đã bị khóa'
            ], 403);
        }

        // 🔥 ĐÃ VÁ BUG #7: Bắt buộc xác thực OTP trước khi cấp token
        // KHỐI LỆNH: Nếu tài khoản này chưa hoàn thành bước xác thực OTP sau khi đăng ký, hệ thống yêu cầu phải xác thực trước.
        if (!$user->is_verified) {
            return response()->json([
                'status'  => false, 
                'message' => 'Vui lòng xác thực OTP'
            ], 403);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Đăng nhập thành công',
            'data'    => [
                'user'  => $user,
                // XỬ LÝ: Khởi tạo Token mới đại diện cho phiên đăng nhập này của người dùng.
                'token' => $user->createToken('VanguardToken')->plainTextToken
            ]
        ]);
    }

    // 📌 3. ĐĂNG XUẤT (LOGOUT)
    // VAI TRÒ: Hủy bỏ token hiện tại của người dùng để kết thúc phiên làm việc một cách an toàn.
    public function logout(Request $request)
    {
        // XỬ LÝ: Xác định token mà client đang dùng để gửi request này và tiến hành xóa nó khỏi cơ sở dữ liệu.
        $request->user()->currentAccessToken()->delete();
        return response()->json(['status' => true, 'message' => 'Đã đăng xuất hệ thống']);
    }

    // 📌 4. GỬI LẠI MÃ OTP (RESEND OTP) - ĐÃ VÁ LỖI
    // VAI TRÒ: Tạo và gửi lại mã OTP mới cho người dùng khi mã cũ bị hết hạn hoặc họ không nhận được tin nhắn.
    public function resendOtp(Request $request)
    {
        // 1. 🔥 ĐÃ FIX LOGIC #1: Yêu cầu truyền và validate loại OTP (type)
        // KHỐI LỆNH: Kiểm tra số điện thoại và loại giao dịch yêu cầu OTP (chỉ chấp nhận 'register' hoặc 'forgot_password').
        $request->validate([
            'phone' => 'required|numeric',
            'type'  => 'required|in:register,forgot_password' 
        ], [
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.numeric'  => 'Số điện thoại chỉ được chứa chữ số.',
            'type.required'  => 'Vui lòng truyền loại giao dịch OTP.',
            'type.in'        => 'Loại giao dịch OTP không hợp lệ.'
        ]);

        // TRUY VẤN: Đảm bảo số điện thoại này phải tồn tại trong bảng người dùng thì mới xử lý gửi mã.
        $user = User::where('phone', $request->phone)->first();
        if (!$user) return response()->json(['status' => false, 'message' => 'SĐT không tồn tại'], 404);

        // 🛡️ VÁ LỖI SPAM: Kiểm tra lần gửi OTP gần nhất chưa qua 60 giây (Chỉ check cùng type)
        // TRUY VẤN: Lấy ra mã OTP mới nhất được tạo dựa theo số điện thoại và loại giao dịch này.
        $lastOtp = DB::table('otp_verifications')
            ->where('phone', $request->phone)
            ->where('type', $request->type)
            ->orderBy('created_at', 'desc')
            ->first();

        // KHỐI LỆNH CHỐNG SPAM: Tính toán khoảng cách thời gian giữa hiện tại và lúc tạo mã gần nhất.
        // Nếu nhỏ hơn 60 giây, hệ thống trả về mã lỗi 429 để ngăn chặn hành vi bấm gửi liên tục làm nghẽn tổng đài.
        if ($lastOtp && now()->diffInSeconds(\Carbon\Carbon::parse($lastOtp->created_at)) < 60) {
            return response()->json([
                'status'  => false, 
                'message' => 'Thao tác quá nhanh. Vui lòng đợi 60 giây để yêu cầu mã mới.'
            ], 429); 
        }

        // TRUY VẤN: Tìm tất cả mã cũ của số điện thoại này cùng thuộc loại (type) này và cập nhật trạng thái đã dùng/hết hạn (is_used = 1) để vô hiệu hóa chúng.
        DB::table('otp_verifications')
            ->where('phone', $request->phone)
            ->where('type', $request->type)
            ->update(['is_used' => 1]);
        
        // 🛡️ VÁ LỖI BRUTE-FORCE: Sử dụng random_int an toàn tuyệt đối về mặt mật mã học
        $otp = random_int(100000, 999999);
        
        // 3. 🔥 Insert mã mới với type động từ request
        // TRUY VẤN: Thêm bản ghi OTP mới lưu vào cơ sở dữ liệu để phục vụ cho lượt kiểm tra tiếp theo.
        DB::table('otp_verifications')->insert([
            'phone'      => $request->phone, 
            'otp_code'   => $otp, 
            'type'       => $request->type, 
            'expires_at' => now()->addMinutes(5), 
            'created_at' => now()
        ]);

        return response()->json(['status' => true, 'message' => 'Đã gửi lại OTP thành công']);
    }

    // 📌 5. XÁC THỰC OTP (VERIFY OTP)
    // VAI TRÒ: Kiểm tra mã OTP do người dùng nhập vào để kích hoạt chính thức tài khoản vừa đăng ký.
    public function verifyOtp(Request $request)
    {
        // KHỐI LỆNH: Yêu cầu client truyền đầy đủ Số điện thoại và Mã OTP cần đối chiếu.
        $request->validate([
            'phone'    => 'required',
            'otp_code' => 'required'
        ]);

        // TRUY VẤN: Tìm kiếm một bản ghi OTP thỏa mãn đồng thời các điều kiện cực kỳ nghiêm ngặt:
        // Trùng số điện thoại, trùng mã số, thuộc loại đăng ký ('register'), chưa từng được sử dụng ('is_used' = 0) và thời hạn sử dụng vẫn chưa trôi qua (expires_at >= thời gian hiện tại).
        $otpRecord = DB::table('otp_verifications')
            ->where('phone', $request->phone)
            ->where('otp_code', $request->otp_code)
            ->where('type', 'register')
            ->where('is_used', 0)
            ->where('expires_at', '>=', now())
            ->first();

        // KHỐI LỆNH: Nếu không tìm thấy bản ghi nào khớp, tức là mã sai hoặc đã quá 5 phút -> Trả về lỗi 400.
        if (!$otpRecord) return response()->json(['status' => false, 'message' => 'OTP không hợp lệ hoặc hết hạn'], 400);

        // TRUY VẤN CẬP NHẬT: 
        // 1. Đánh dấu mã OTP này đã được dùng xong (is_used = 1) để không ai có thể sử dụng lại nó lần hai.
        // 2. Chuyển trạng thái của người dùng sở hữu số điện thoại này thành đã xác thực thành công (is_verified = 1).
        DB::table('otp_verifications')->where('id', $otpRecord->id)->update(['is_used' => 1]);
        User::where('phone', $request->phone)->update(['is_verified' => 1]);

        return response()->json(['status' => true, 'message' => 'Xác thực tài khoản thành công!']);
    }

    // 📌 6. TẠO MÃ QUÊN MẬT KHẨU
    // VAI TRÒ: Khởi tạo mã OTP đặc biệt dành riêng cho chức năng khôi phục mật khẩu khi người dùng quên mật khẩu đăng nhập.
    public function createCodeResetPassword(Request $request)
    {
        // KHỐI LỆNH: Đảm bảo số điện thoại truyền lên đúng định dạng số.
        $request->validate([
            'phone' => 'required|numeric'
        ], [
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.numeric'  => 'Số điện thoại chỉ được chứa chữ số.'
        ]);

        // TRUY VẤN: Kiểm tra xem số điện thoại này có thuộc về tài khoản nào trong hệ thống không. Nếu không có thì không thể khôi phục.
        $user = User::where('phone', $request->phone)->first();
        if (!$user) return response()->json(['status' => false, 'message' => 'Số điện thoại không tồn tại'], 404);

        // XỬ LÝ: Tạo mã ngẫu nhiên 6 chữ số an toàn (giống thuật toán ở hàm signup).
        try {
            $otp = random_int(100000, 999999);
        } catch (\Exception $e) {
            $otp = mt_rand(100000, 999999);
        }
        
        // TRUY VẤN: Lưu mã vào bảng OTP nhưng với nhãn phân loại hành động là 'forgot_password'.
        DB::table('otp_verifications')->insert([
            'phone'      => $request->phone, 
            'otp_code'   => $otp, 
            'type'       => 'forgot_password', 
            'expires_at' => now()->addMinutes(5)
        ]);

        return response()->json(['status' => true, 'message' => 'Đã tạo mã khôi phục và gửi về số điện thoại']);
    }

    // 📌 7. KIỂM TRA MÃ KHÔI PHỤC
    // VAI TRÒ: Xác thực nhanh xem mã OTP quên mật khẩu người dùng nhập vào có hợp lệ hay không trước khi chuyển họ sang bước đặt mật khẩu mới.
    public function checkCodeResetPassword(Request $request)
    {
        // KHỐI LỆNH: Bắt buộc điền số điện thoại và mã khôi phục phải có độ dài chính xác là 6 chữ số.
        $request->validate([
            'phone' => 'required|numeric', 
            'code'  => 'required|digits:6'
        ], [
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'code.required'  => 'Vui lòng nhập mã khôi phục.',
            'code.digits'    => 'Mã khôi phục phải có đúng 6 chữ số.'
        ]);

        // TRUY VẤN: Xác nhận số điện thoại tồn tại.
        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Số điện thoại không tồn tại trong hệ thống!'
            ], 404);
        }

        // TRUY VẤN: So khớp mã OTP trong DB, bắt buộc phải là loại 'forgot_password', chưa dùng và còn hạn.
        $otpRecord = DB::table('otp_verifications')
            ->where('phone', $request->phone)
            ->where('otp_code', $request->code)
            ->where('type', 'forgot_password')
            ->where('is_used', 0)
            ->where('expires_at', '>=', now())
            ->first();

        // KHỐI LỆNH: Nếu mã nhập sai hoặc hết hạn thì chặn lại luôn.
        if (!$otpRecord) return response()->json(['status' => false, 'message' => 'Mã khôi phục không hợp lệ hoặc đã hết hạn'], 400);
        
        return response()->json(['status' => true, 'message' => 'Mã khôi phục hợp lệ.']);
    }

    // 📌 8. ĐẶT LẠI MẬT KHẨU MỚI
    // VAI TRÒ: Sau khi đã xác thực OTP thành công, tiến hành ghi đè mật khẩu mới của người dùng vào cơ sở dữ liệu.
    public function resetPassword(Request $request)
    {
        // KHỐI LỆNH: Ràng buộc thông tin đầu vào, mật khẩu mới yêu cầu tối thiểu từ 6 ký tự trở lên.
        $request->validate([
            'phone'        => 'required|numeric',
            'otp_code'     => 'required|digits:6',
            'new_password' => 'required|min:6'
        ], [
            'phone.required'        => 'Vui lòng nhập số điện thoại.',
            'phone.numeric'         => 'Số điện thoại chỉ được chứa chữ số.',
            'otp_code.required'     => 'Vui lòng nhập mã xác thực OTP.',
            'otp_code.digits'       => 'Mã OTP phải có đúng 6 chữ số.',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min'      => 'Mật khẩu phải có ít nhất 6 ký tự.'
        ]);

        // TRUY VẤN: Xác định tài khoản người dùng cần đổi mật khẩu thông qua số điện thoại.
        $user = User::where('phone', $request->phone)->first();
        
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Số điện thoại không tồn tại trong hệ thống!'
            ], 404);
        }

        // TRUY VẤN: Đối chiếu lại mã OTP một lần nữa để đảm bảo tính an toàn bảo mật tuyệt đối trước khi cập nhật dữ liệu nhạy cảm.
        $otpRecord = DB::table('otp_verifications')
            ->where('phone', $user->phone)
            ->where('otp_code', $request->otp_code)
            ->where('type', 'forgot_password')
            ->where('is_used', 0)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'status'  => false,
                'message' => 'Mã OTP không hợp lệ, đã được sử dụng hoặc đã hết hạn!'
            ], 400);
        }

        // XỬ LÝ: Tiến hành mã hóa mật khẩu mới và lưu trực tiếp vào cơ sở dữ liệu của người dùng đó.
        $user->password = Hash::make($request->new_password);
        $user->save();

        // TRUY VẤN CẬP NHẬT: Vô hiệu hóa mã OTP này để không ai có thể đem đi dùng lại được nữa.
        DB::table('otp_verifications')
            ->where('id', $otpRecord->id)
            ->update(['is_used' => 1]);

        return response()->json([
            'status'  => true, 
            'message' => 'Đổi mật khẩu thành công!'
        ]);
    }

    // 📌 9. ĐỔI MẬT KHẨU KHI ĐANG ĐĂNG NHẬP
    // VAI TRÒ: Cho phép người dùng chủ động thay đổi mật khẩu định kỳ ngay bên trong ứng dụng khi phiên đăng nhập của họ vẫn còn hiệu lực.
    public function changePassword(Request $request)
    {
        // KHỐI LỆNH: Yêu cầu điền mật khẩu cũ, mật khẩu mới và sử dụng tính năng 'confirmed' (bắt buộc client gửi thêm trường 'new_password_confirmation' trùng khớp với mật khẩu mới).
        $request->validate([
            'old_password' => 'required', 
            'new_password' => 'required|min:6|confirmed'
        ]);
        
        // BIẾN QUAN TRỌNG: Lấy thông tin thực tế của thực thể người dùng đang gửi yêu cầu đăng nhập này.
        $user = $request->user();

        // KHỐI LỆNH: Kiểm tra mật khẩu cũ. Nếu người dùng nhập sai mật khẩu hiện tại thì không được phép đổi sang mật khẩu mới.
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'status'  => false, 
                'message' => 'Mật khẩu cũ không chính xác'
            ], 400);
        }
        
        // XỬ LÝ: Cập nhật mã hóa mật khẩu mới.
        $user->password = Hash::make($request->new_password);
        
        // 🔥 ĐÃ FIX LOGIC #5: Đánh dấu user đã qua lần đăng nhập đầu tiên
        // Ý NGHĨA: Chuyển cờ `is_first_login` về giá trị 0, báo hiệu hệ thống ghi nhận tài khoản này đã hoàn tất việc cập nhật đổi mật khẩu lần đầu (bỏ qua các thông báo nhắc nhở đổi mật khẩu mặc định).
        $user->is_first_login = 0; 
        $user->save();
        
        return response()->json([
            'status'  => true, 
            'message' => 'Cập nhật mật khẩu thành công'
        ]);
    }
}