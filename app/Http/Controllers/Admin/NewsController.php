<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * MỤC ĐÍCH CỦA FILE:
 * File này là Controller chịu trách nhiệm quản lý module Tin tức / Bài viết (News).
 * Xử lý các nghiệp vụ nâng cao bao gồm: Tự động tối ưu hóa đường dẫn (Slug) chống trùng lặp,
 * quản lý trạng thái bài viết (Bản nháp, Xuất bản, Ẩn) và hẹn giờ đăng bài tự động (Scheduled) theo thời gian thực.
 */
class NewsController extends Controller
{
    // ==========================================
    // 1. TRANG DANH SÁCH TIN TỨC (INDEX)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: index()
     * Lấy danh sách tin tức mới nhất, thực hiện phân trang và hiển thị ra màn hình quản lý.
     */
    public function index()
    {
        // latest(): Sắp xếp bài viết mới nhất lên đầu (tương đương với orderBy('created_at', 'desc'))
        // paginate(20): Lấy 20 bài viết trên mỗi trang
        $news = News::latest()->paginate(20);

        return view('admin.news.index', compact('news'));
    }

    // ==========================================
    // 2. TRANG GIAO DIỆN THÊM MỚI (CREATE)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: create()
     * Hiển thị Form để Admin viết bài tin tức mới.
     */
    public function create()
    {
        return view('admin.news.create');
    }

    // ==========================================
    // 3. XỬ LÝ LƯU TIN TỨC MỚI (STORE)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: store()
     * Tiếp nhận bài viết mới, tự động phân tích thời gian đăng bài để quyết định trạng thái (status) phù hợp,
     * xử lý tải ảnh đại diện và lưu vào Database kèm chuỗi định danh độc bản (Unique Slug).
     */
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | KHỐI KIỂM TRA DỮ LIỆU ĐẦU VÀO + TÙY BIẾN LỖI
        |--------------------------------------------------------------------------
        | - status: Bắt buộc thuộc một trong bốn giá trị quy định: draft, published, hidden, scheduled.
        | - published_at: Nếu điền thì bắt buộc phải là một mốc thời gian lớn hơn hoặc bằng hiện tại ('after_or_equal:now').
        */
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'required|in:draft,published,hidden,scheduled',
            'published_at' => 'nullable|date|after_or_equal:now',
        ], [
            'published_at.after_or_equal' => 'Ngày đăng không được nhỏ hơn thời điểm hiện tại.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | KHỐI LOGIC XỬ LÝ TRẠNG THÁI THEO THỜI GIAN ĐĂNG (SCHEDULE LOGIC)
        |--------------------------------------------------------------------------
        */
        $publishedAt = $request->published_at;
        $status = $request->status;

        if ($publishedAt) {
            $publishedAtCarbon = Carbon::parse($publishedAt);

            if ($publishedAtCarbon->isFuture()) {
                // Tình huống 1: Thời gian chọn nằm trong tương lai -> Tự động chuyển trạng thái thành 'scheduled'
                // Bài viết sẽ được giữ lại và chờ hệ thống Cron Job / Scheduler kích hoạt xuất bản sau.
                $status = 'scheduled';
            } else {
                // Tình huống 2: Thời gian <= hiện tại -> Đăng ngay lập tức và lấy mốc thời gian hiện tại của hệ thống
                $status = 'published';
                $publishedAt = now();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | KHỐI XỬ LÝ LƯU ẢNH ĐẠI DIỆN VÀO THƯ MỤC PUBLIC
        |--------------------------------------------------------------------------
        | Sử dụng phương thức di chuyển file truyền thống sang thư mục 'public/uploads/news/'
        | Tên file được cấu hình nối chuỗi `time()` ở đầu để tránh tối đa việc ghi đè tệp tin trùng tên.
        */
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/news'), $filename);
            $thumbnailPath = 'uploads/news/' . $filename;
        }

        /*
        |--------------------------------------------------------------------------
        | KHỐI KHỞI TẠO BẢN GHI TIN TỨC VỚI SLUG PHÂN TÁCH (UNIQID)
        |--------------------------------------------------------------------------
        | Đoạn code sinh slug: Str::slug($request->title) . '-' . uniqid()
        | Đảm bảo tạo ra một chuỗi slug không bao giờ bị trùng bằng cách nối thêm mã hash ngẫu nhiên ở cuối bài.
        */
        News::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . uniqid(),
            'content' => $request->content,
            'thumbnail' => $thumbnailPath,
            'status' => $status,
            'published_at' => $publishedAt,
        ]);

        return redirect()
            ->route('admin.news.index')
            ->with('success', 'Thêm tin tức thành công!');
    }

    // ==========================================
    // 4. XEM CHI TIẾT TIN TỨC (SHOW)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: show()
     * Hiển thị nội dung chi tiết của một bài viết cụ thể dựa trên ID.
     */
    public function show($id)
    {
        $news = News::findOrFail($id);
        return view('admin.news.show', compact('news'));
    }

    // ==========================================
    // 5. TRANG GIAO DIỆN CHỈNH SỬA (EDIT)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: edit()
     * Lấy thông tin bài viết cũ đổ vào form chỉnh sửa cho Admin.
     */
    public function edit($id)
    {
        $news = News::findOrFail($id);
        return view('admin.news.edit', compact('news'));
    }

    // ==========================================
    // 6. XỬ LÝ CẬP NHẬT TIN TỨC (UPDATE)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: update()
     * Cập nhật các sửa đổi của bài viết. Chứa thuật toán vòng lặp thông minh để kiểm tra 
     * và tự động tăng số thứ tự đuôi nếu tiêu đề mới vô tình trùng slug với các bài viết khác.
     */
    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);

        // Kiểm tra hợp lệ dữ liệu chỉnh sửa
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published,hidden',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'published_at' => 'nullable|date',
        ]);

        /*
        |--------------------------------------------------------------------------
        | THUẬT TOÁN TỰ ĐỘNG TĂNG ĐUÔI SLUG NẾU BỊ TRÙNG (WHILE LOOP SLUG CHỐNG TRÙNG)
        |--------------------------------------------------------------------------
        | Ý nghĩa: Khác với hàm store() dùng uniqid(), hàm update() của bạn kiểm tra sự tồn tại trong DB.
        | Vòng lặp while sẽ quét liên tục, nếu tìm thấy slug trùng trong bảng (ngoại trừ chính ID bài này),
        | nó sẽ cộng thêm số thứ tự tăng dần ở cuối (Ví dụ: tin-tuc-uav, tin-tuc-uav-1, tin-tuc-uav-2...).
        */
        $baseSlug = Str::slug($request->title);
        $slug = $baseSlug;
        $count = 1;

        while (News::where('slug', $slug)->where('id', '!=', $news->id)->exists()) {
            $slug = $baseSlug . '-' . $count;
            $count++;
        }

        /*
        |--------------------------------------------------------------------------
        | KHỐI XỬ LÝ ẢNH ĐẠI DIỆN KHI CẬP NHẬT
        |--------------------------------------------------------------------------
        | Lưu ý: Khối này đang sử dụng Storage công khai (khác với hàm store() phía trên dùng public_path).
        | Nếu có tệp tin ảnh mới, hệ thống sẽ lưu vào đĩa 'public' thông qua phân mục 'news'.
        */
        $thumbnail = $news->thumbnail;
        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail')->store('news', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | TIẾN HÀNH CẬP NHẬT DỮ LIỆU
        |--------------------------------------------------------------------------
        */
        $news->update([
            'title' => $request->title,
            'slug' => $slug,
            'thumbnail' => $thumbnail,
            'content' => $request->content,
            'status' => $request->status,
            'published_at' => $request->published_at ? Carbon::parse($request->published_at) : null,
        ]);

        return redirect()
            ->route('admin.news.index')
            ->with('success', 'Cập nhật thành công');
    }

    // ==========================================
    // 7. XỬ LÝ XÓA TIN TỨC (DESTROY)
    // ==========================================

    /**
     * VAI TRÒ CỦA METHOD: destroy()
     * Xóa vĩnh viễn bài viết ra khỏi hệ thống Database dựa trên ID.
     */
    public function destroy($id)
    {
        $news = News::findOrFail($id);
        $news->delete();

        return redirect()
            ->route('admin.news.index')
            ->with('success', 'Xóa thành công');
    }
}