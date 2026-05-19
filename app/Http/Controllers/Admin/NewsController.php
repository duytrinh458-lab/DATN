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
        $news = News::latest()->paginate(10);

        return view('admin.news.index', compact('news'));
    }

    public function create()
    {
        return view('admin.news.create');
    }

    public function store(Request $request)
    {
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
            News::where('slug', $slug)->exists()
        ) {

            $slug = $baseSlug . '-' . $count;

            $count++;
        }

        /*
        |--------------------------------------------------------------------------
        | UPLOAD THUMBNAIL
        |--------------------------------------------------------------------------
        */
        $thumbnail = null;

        if ($request->hasFile('thumbnail')) {

            $thumbnail = $request->file('thumbnail')
                ->store('news', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | CREATE NEWS
        |--------------------------------------------------------------------------
        */
        News::create([

            'title' => $request->title,

            'slug' => $slug,

            'thumbnail' => $thumbnail,

            'content' => $request->content,

            'status' => $request->status ?? 'draft',

            'published_at' => $request->published_at
                ? Carbon::parse($request->published_at)
                : null,
        ]);

        return redirect()
            ->route('admin.news.index')
            ->with(
                'success',
                'Thêm tin tức thành công'
            );
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