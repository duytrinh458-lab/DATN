<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::latest()->paginate(20);

        return view('admin.news.index', compact('news'));
    }

    public function create()
    {
        return view('admin.news.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'status' => 'required|in:draft,published,hidden,scheduled',
        'published_at' => 'nullable|date|after_or_equal:now',
    ], [
        'published_at.after_or_equal' => 'Ngày đăng không được nhỏ hơn thời điểm hiện tại.',
    ]);

    // Logic xử lý status dựa trên published_at
    $publishedAt = $request->published_at;
    $status = $request->status;

    if ($publishedAt) {
        $publishedAtCarbon = \Carbon\Carbon::parse($publishedAt);

        if ($publishedAtCarbon->isFuture()) {
            // Nếu chọn đăng trong tương lai -> tạm để là 'scheduled' (hoặc giữ draft)
            // và chờ scheduler tự động chuyển sang published
            $status = 'scheduled';
        } else {
            // Nếu thời gian <= hiện tại -> đăng ngay
            $status = 'published';
            $publishedAt = now();
        }
    }

    // Xử lý thumbnail
    $thumbnailPath = null;
    if ($request->hasFile('thumbnail')) {
        $file = $request->file('thumbnail');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/news'), $filename);
        $thumbnailPath = 'uploads/news/' . $filename;
    }

    News::create([
        'title' => $request->title,
        'slug' => \Illuminate\Support\Str::slug($request->title) . '-' . uniqid(),
        'content' => $request->content,
        'thumbnail' => $thumbnailPath,
        'status' => $status,
        'published_at' => $publishedAt,
    ]);

    return redirect()->route('admin.news.index')
                      ->with('success', 'Thêm tin tức thành công!');
}

    public function show($id)
    {
        $news = News::findOrFail($id);

        return view('admin.news.show', compact('news'));
    }

    public function edit($id)
    {
        $news = News::findOrFail($id);

        return view('admin.news.edit', compact('news'));
    }

    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | VALIDATE
        |--------------------------------------------------------------------------
        */
        $request->validate([

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'content' => [
                'required',
                'string',
            ],

            'status' => [
                'required',
                'in:draft,published,hidden',
            ],

            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'published_at' => [
                'nullable',
                'date',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | AUTO CREATE UNIQUE SLUG
        |--------------------------------------------------------------------------
        */
        $baseSlug = Str::slug($request->title);

        $slug = $baseSlug;

        $count = 1;

        while (
            News::where('slug', $slug)
                ->where('id', '!=', $news->id)
                ->exists()
        ) {

            $slug = $baseSlug . '-' . $count;

            $count++;
        }

        /*
        |--------------------------------------------------------------------------
        | THUMBNAIL
        |--------------------------------------------------------------------------
        */
        $thumbnail = $news->thumbnail;

        if ($request->hasFile('thumbnail')) {

            $thumbnail = $request->file('thumbnail')
                ->store('news', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE NEWS
        |--------------------------------------------------------------------------
        */
        $news->update([

            'title' => $request->title,

            'slug' => $slug,

            'thumbnail' => $thumbnail,

            'content' => $request->content,

            'status' => $request->status,

            'published_at' => $request->published_at
                ? Carbon::parse($request->published_at)
                : null,
        ]);

        return redirect()
            ->route('admin.news.index')
            ->with(
                'success',
                'Cập nhật thành công'
            );
    }

    public function destroy($id)
    {
        $news = News::findOrFail($id);

        $news->delete();

        return redirect()
            ->route('admin.news.index')
            ->with(
                'success',
                'Xóa thành công'
            );
    }
}