<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    // ================= CHỨC NĂNG HIỂN THỊ GIAO DIỆN (VIEW) =================
    
    /**
     * Chức năng: Hiển thị màn hình Đăng nhập cho người dùng.
     */
    public function showLogin() {
        return view('Login.login');
    }

    /**
     * Chức năng: Hiển thị màn hình Đăng ký tài khoản mới.
     */
    public function showRegister() {
        return view('Login.register');
    }

    /**
     * Chức năng: Hiển thị màn hình Nhập số điện thoại khi quên mật khẩu.
     */
    public function showForgot() {
        return view('Login.forgot');
    }

    /**
     * Chức năng: Hiển thị màn hình Đổi mật khẩu.
     * Bảo mật: Kiểm tra nếu người dùng chưa đăng nhập thì không cho vào và đá về trang đăng nhập.
     */
    public function showChangePasswordForm()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        return view('Login.change-password');
    }

    // ================= QUY TRÌNH ĐĂNG KÝ TÀI KHOẢN (BẰNG OTP) =================
    
    /**
     * Bước 1: Nhập số điện thoại để nhận mã OTP đăng ký.
     */
    public function sendOtpRegister(Request $request)
    {
        // 1. Kiểm tra dữ liệu: Số điện thoại phải đúng định dạng (10-11 số) và chưa từng được đăng ký trước đây.
        $request->validate([
            'phone' => 'required|numeric|digits_between:10,11|unique:users,phone'
        ],[
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.numeric' => 'Số điện thoại chỉ được chứa chữ số.',
            'phone.digits_between' => 'Số điện thoại phải từ 10 đến 11 số.',
            'phone.unique' => 'Số điện thoại này đã được đăng ký.',
        ]);

        // 2. Chống Spam: Kiểm tra xem số này vừa yêu cầu gửi mã chưa. Nếu chưa đủ 60 giây thì bắt đợi (tránh tốn chi phí gửi SMS/tải hệ thống).
        $lastOtp = DB::table('otp_verifications')
            ->where('phone', $request->phone)
            ->where('type', 'register')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastOtp && now()->diffInSeconds(\Carbon\Carbon::parse($lastOtp->created_at)) < 60) {
            return back()->with('error', 'Hệ thống đang xử lý. Vui lòng đợi 60 giây trước khi yêu cầu gửi lại mã mới.');
        }

        // 3. Bảo mật mã cũ: Vô hiệu hóa (hủy bỏ) toàn bộ các mã OTP đăng ký đã gửi trước đó của số điện thoại này để tránh dùng lại mã cũ.
        DB::table('otp_verifications')
            ->where('phone', $request->phone)
            ->where('type', 'register')
            ->where('is_used', 0)
            ->update(['is_used' => 1]);

        // 4. Tạo mã OTP an toàn: Hệ thống tự sinh ra một dãy số ngẫu nhiên gồm 6 chữ số với thuật toán mã hóa bảo mật cao.
        try {
                $otp = random_int(100000, 999999);
            } catch (\Exception $e) {
                $otp = mt_rand(100000, 999999); 
            }

        // 5. Lưu trữ tạm thời: Lưu mã OTP vào cơ sở dữ liệu, đặt thời gian hết hạn là 5 phút.
        DB::table('otp_verifications')->insert([
            'phone' => $request->phone,
            'otp_code' => $otp,
            'type' => 'register',
            'is_used' => 0,
            'expires_at' => now()->addMinutes(5), 
            'created_at' => now()
        ]);

        // 6. Ghi nhớ phiên làm việc: Lưu số điện thoại vào bộ nhớ tạm (Session) để phục vụ cho bước xác thực tiếp theo.
        session(['phone_step1' => $request->phone]);

        // 7. Nhật ký hệ thống: Chỉ hiển thị mã OTP trong file log khi đang chạy thử nghiệm (Dev) để lập trình viên kiểm tra, khi chạy thật sẽ tự ẩn đi.
        if (config('app.debug')) {
            Log::info('Mã OTP Đăng ký của SĐT ' . $request->phone . ' là: ' . $otp);
        }

        return back()->with(
            'success',
            'Mã xác thực OTP đã được gửi đến số điện thoại của bạn.'
        );
    }

    /**
     * Bước 2: Xác thực mã OTP và Tạo tài khoản chính thức.
     */
    public function verifyOtpRegister(Request $request)
    {
        // 1. Kiểm tra xem người dùng đã qua Bước 1 chưa. Nếu chưa hoặc quá lâu thì bắt quay lại từ đầu.
        $phone = session('phone_step1');

        if (!$phone) {
            return redirect('/register')->with(
                'error',
                'Phiên đăng ký đã hết hạn. Vui lòng thử lại.'
            );
        }

        // 2. Chống dò mã (Brute Force): Nếu người dùng cố tình nhập sai liên tiếp quá 5 lần, khóa chức năng nhập của số điện thoại này trong 15 phút.
        $key = 'verify-otp-register:' . $phone;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', "Bạn đã nhập sai quá nhiều lần. Vui lòng thử lại sau {$seconds} giây.");
        }

        // 3. Kiểm tra thông tin nhập vào: Tên, Email (phải duy nhất), Mật khẩu (tối thiểu 6 ký tự), Mã OTP (đúng 6 số).
        $request->validate([
            'otp_code' => 'required|digits:6',
            'full_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ],[
            'otp_code.required' => 'Vui lòng nhập mã OTP.',
            'otp_code.digits' => 'Mã OTP phải gồm đúng 6 số.',
            'full_name.string' => 'Họ và tên không hợp lệ.',
            'full_name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã tồn tại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);

        // 4. Đối chiếu mã OTP: Tìm trong cơ sở dữ liệu xem mã người dùng nhập có khớp, đúng số điện thoại, chưa từng dùng và còn hạn không.
        $otp = DB::table('otp_verifications')
            ->where('phone', $phone)
            ->where('otp_code', $request->otp_code)
            ->where('type', 'register')
            ->where('is_used', 0)
            ->where('expires_at', '>', now())
            ->first();

        // 5. Xử lý khi sai mã: Ghi nhận 1 lần nhập sai, báo cho người dùng biết còn bao nhiêu lần thử.
        if (!$otp) {
            RateLimiter::hit($key, 900); // Lưu lịch sử phạt trong 15 phút (900 giây)
            $attemptsLeft = RateLimiter::remaining($key, 5);
            return back()->with(
                'error',
                "Mã OTP không chính xác hoặc đã hết hạn. Bạn còn {$attemptsLeft} lần thử."
            );
        }

        // 6. Xử lý khi đúng mã: Xóa bỏ lịch sử phạt nhập sai trước đó.
        RateLimiter::clear($key);

        // 7. Quy trình tạo tài khoản an toàn (Transaction): Đảm bảo các hành động dưới đây phải thành công ĐỒNG THỜI. Nếu một cái lỗi, hệ thống hủy toàn bộ để tránh dữ liệu rác.
        DB::beginTransaction();

        try {
            // A. Khởi tạo thông tin khách hàng mới (Mật khẩu được mã hóa an toàn dạng Hash).
            $user = User::create([
                'username' => explode('@', $request->email)[0], // Tự động lấy phần trước chữ @ của email làm tên đăng nhập
                'full_name' => $request->full_name ?? 'User',
                'email' => $request->email,
                'phone' => $phone,
                'password' => Hash::make($request->password),
                'role' => 'customer', // Mặc định là tài khoản khách mua hàng
                'is_verified' => 1,
                'is_first_login' => 1, // Đánh dấu đây là lần đầu đăng nhập để có thể yêu cầu đổi mật khẩu nếu cần
                'status' => 'active',
            ]);

            // B. Tự động kích hoạt một ví điện tử cá nhân cho tài khoản này với số dư ban đầu bằng 0.
            DB::table('wallets')->insert([
                'user_id' => $user->id,
                'balance' => 0,
                'updated_at' => now()
            ]);

            // C. Đánh dấu mã OTP này đã được sử dụng thành công (không thể dùng lại lần 2).
            DB::table('otp_verifications')
                ->where('id', $otp->id)
                ->update(['is_used' => 1]);

            // Xác nhận hoàn tất chuỗi hành động thành công.
            DB::commit();

            // Xóa số điện thoại khỏi bộ nhớ tạm sau khi đã đăng ký xong.
            session()->forget('phone_step1');

            return redirect('/login')->with(
                'success',
                'Tài khoản của bạn đã được tạo thành công.'
            );

        } catch (\Exception $e) {
            // Nếu có bất kỳ lỗi phát sinh nào (ví dụ: mất kết nối mạng giữa chừng), hệ thống sẽ khôi phục lại trạng thái ban đầu như chưa có gì xảy ra.
            DB::rollBack();

            return back()->with(
                'error',
                'Đã xảy ra lỗi hệ thống. Vui lòng thử lại.'
            );
        }
    }

    // ================= CHỨC NĂNG ĐĂNG NHẬP =================
    
    /**
     * Chức năng: Kiểm tra thông tin và cho phép người dùng vào hệ thống.
     */
    public function login(Request $request)
    {
        // 1. Kiểm tra dữ liệu đầu vào.
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ],[
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);

        // 2. Xác thực tài khoản: So khớp Email và Mật khẩu trong cơ sở dữ liệu.
        if (Auth::attempt($request->only('email', 'password'))) {

            // Bảo mật: Làm mới lại thẻ định danh phiên làm việc (Session ID) để chống hack/chiếm đoạt quyền đăng nhập.
            $request->session()->regenerate();

            $user = Auth::user();

            // Điều hướng 1: Nếu tài khoản này được tạo bởi Admin và đăng nhập lần đầu, bắt buộc chuyển hướng đến trang đổi mật khẩu mới.
            if ($user->is_first_login) {
                return redirect()->route('password.change');
            }

            // Điều hướng 2: Phân quyền người dùng. Nếu là Quản trị viên (Admin) -> vào Trang quản trị; Nếu là Khách hàng -> về Trang chủ.
            return $user->role === 'admin'
                ? redirect()->route('admin.dashboard')
                : redirect()->route('home');
        }

        // Trả về lỗi nếu nhập sai thông tin.
        return back()->with(
            'error',
            'Email hoặc mật khẩu không chính xác.'
        );
    }

    // ================= CHỨC NĂNG ĐỔI MẬT KHẨU =================
    
    /**
     * Chức năng: Cho phép người dùng đang đăng nhập tự thay đổi mật khẩu của mình.
     */
    public function updatePassword(Request $request)
    {
        // Bảo mật: Chặn đứng nếu người dùng chưa đăng nhập mà cố tình vào link này.
        if (!Auth::check()) {
            return redirect('/login');
        }

        // Kiểm tra: Mật khẩu mới phải từ 6 ký tự và ô "Nhập lại mật khẩu" phải khớp nhau.
        $request->validate([
            'password' => 'required|min:6|confirmed'
        ],[
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        // Tìm tài khoản hiện tại, mã hóa mật khẩu mới và lưu lại, đồng thời tắt trạng thái "đăng nhập lần đầu".
        $user = User::find(Auth::id());
        $user->password = Hash::make($request->password);
        $user->is_first_login = 0;
        $user->save();

        // Chuyển hướng người dùng về đúng trang giao diện theo quyền (Admin hoặc Khách hàng).
        return $user->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('home');
    }

    // ================= QUY TRÌNH QUÊN MẬT KHẨU (KHÔI PHỤC QUA OTP) =================
    
    /**
     * Bước 1: Nhập số điện thoại để nhận mã OTP lấy lại mật khẩu.
     */
    public function sendOtpForgotPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric|digits_between:10,11'
        ],[
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.numeric' => 'Số điện thoại chỉ được chứa chữ số.',
            'phone.digits_between' => 'Số điện thoại phải từ 10 đến 11 số.',
        ]);

        // Kiểm tra xem số điện thoại này đã có tài khoản trên hệ thống chưa. Nếu chưa thì không gửi OTP.
        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return back()->with(
                'error',
                'Không tìm thấy tài khoản với số điện thoại này.'
            );
        }

        // Chống Spam: Chặn gửi liên tục dưới 60 giây đối với luồng quên mật khẩu.
        $lastOtp = DB::table('otp_verifications')
            ->where('phone', $request->phone)
            ->where('type', 'forgot_password')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastOtp && now()->diffInSeconds(\Carbon\Carbon::parse($lastOtp->created_at)) < 60) {
            return back()->with('error', 'Hệ thống đang xử lý. Vui lòng đợi 60 giây trước khi yêu cầu gửi lại mã mới.');
        }

        // Hủy bỏ toàn bộ các mã OTP lấy lại mật khẩu cũ chưa dùng của số điện thoại này.
        DB::table('otp_verifications')
            ->where('phone', $request->phone)
            ->where('type', 'forgot_password')
            ->where('is_used', 0)
            ->update(['is_used' => 1]);

        // Tạo mã OTP gồm 6 chữ số bảo mật cao.
        try {
                $otp = random_int(100000, 999999);
                } catch (\Exception $e) {
                $otp = mt_rand(100000, 999999); 
            }

        // Lưu mã OTP quên mật khẩu vào hệ thống với thời hạn 5 phút.
        DB::table('otp_verifications')->insert([
            'phone' => $request->phone,
            'otp_code' => $otp,
            'type' => 'forgot_password',
            'is_used' => 0,
            'expires_at' => now()->addMinutes(5),
            'created_at' => now()
        ]);

        // Ghi nhớ số điện thoại đang thực hiện quên mật khẩu vào bộ nhớ Session hệ thống.
        session(['forgot_phone' => $request->phone]);

        if (config('app.debug')) {
            Log::info('Mã OTP Quên mật khẩu của SĐT ' . $request->phone . ' là: ' . $otp);
        }

        return back()->with(
            'success',
            'Mã xác thực OTP đã được gửi đến số điện thoại của bạn.'
        );
    }

    /**
     * Bước 2: Kiểm tra mã OTP quên mật khẩu và đặt lại mật khẩu mới.
     */
    public function verifyOtpForgotPassword(Request $request)
    {
        // Kiểm tra tính hợp lệ của phiên làm việc để tránh việc người dùng giả mạo dữ liệu gửi lên từ bên ngoài.
        $phone = session('forgot_phone');

        if (!$phone) {
            return redirect('/forgot')->with(
                'error',
                'Phiên làm việc đã hết hạn. Vui lòng thử lại.'
            );
        }

        // Chống dò mã (Brute Force): Nhập sai quá 5 lần sẽ khóa luồng này trong 15 phút.
        $key = 'verify-otp-forgot:' . $phone;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', "Bạn đã nhập sai quá nhiều lần. Vui lòng thử lại sau {$seconds} giây.");
        }

        $request->validate([
            'phone' => 'required',
            'otp_code' => 'required|digits:6',
            'new_password' => 'required|min:6'
        ],[
            'phone.required' => 'Thiếu số điện thoại.',
            'otp_code.required' => 'Vui lòng nhập mã OTP.',
            'otp_code.digits' => 'Mã OTP phải gồm đúng 6 số.',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
        ]);

        // Kiểm tra xem mã OTP nhập vào có đúng, khớp loại "quên mật khẩu", chưa dùng và còn hạn không.
        $otp = DB::table('otp_verifications')
            ->where('phone', $phone)
            ->where('otp_code', $request->otp_code)
            ->where('type', 'forgot_password')
            ->where('is_used', 0)
            ->where('expires_at', '>', now())
            ->first();

        // Nếu sai OTP: Ghi nhận lỗi và báo số lần thử còn lại cho người dùng.
        if (!$otp) {
            RateLimiter::hit($key, 900);
            $attemptsLeft = RateLimiter::remaining($key, 5);
            return back()->with(
                'error',
                "Mã OTP không chính xác hoặc đã hết hạn. Bạn còn {$attemptsLeft} lần thử."
            );
        }

        // Nếu đúng OTP: Xóa lịch sử đếm lỗi.
        RateLimiter::clear($key);

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return back()->with(
                'error',
                'Không tìm thấy tài khoản với số điện thoại này.'
            );
        }

        // Tiến hành cập nhật mật khẩu mới (được băm bảo mật Hash) và lưu lại vào database.
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Vô hiệu hóa mã OTP vừa dùng.
        DB::table('otp_verifications')
            ->where('id', $otp->id)
            ->update(['is_used' => 1]);

        // Xóa thông tin số điện thoại trong bộ nhớ tạm để kết thúc luồng.
        session()->forget('forgot_phone');

        return redirect('/login')->with(
            'success',
            'Mật khẩu đã được cập nhật thành công.'
        );
    }

    // ================= CHỨC NĂNG ĐĂNG XUẤT =================
    
    /**
     * Chức năng: Đăng xuất người dùng ra khỏi hệ thống một cách an toàn.
     */
    public function logout(Request $request)
    {
        // 1. Xóa trạng thái đăng nhập của người dùng hiện tại trên hệ thống.
        Auth::logout();

        // 2. Hủy bỏ và làm trống toàn bộ dữ liệu phiên làm việc (Session) cũ.
        $request->session()->invalidate();

        // 3. Đổi lại mã Token bảo mật (CSRF Token) để đảm bảo không ai có thể dùng lại phiên làm việc cũ này để hack.
        $request->session()->regenerateToken();

        // 4. Đưa người dùng quay lại màn hình Đăng nhập.
        return redirect('/login');
    }
}