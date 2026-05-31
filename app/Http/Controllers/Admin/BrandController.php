<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::orderBy('id', 'desc')->paginate(10);

        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100|unique:brands,name',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $logo = null;

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo')
                            ->store('brands', 'public');
        }

        Brand::create([
            'name' => $request->name,
            'logo' => $logo,
        ]);

        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Thêm thương hiệu thành công');
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|max:100|unique:brands,name,' . $brand->id,
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $logo = $brand->logo;

        if ($request->hasFile('logo')) {

            if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                Storage::disk('public')->delete($brand->logo);
            }

            $logo = $request->file('logo')
                            ->store('brands', 'public');
        }

        $brand->update([
            'name' => $request->name,
            'logo' => $logo,
        ]);

        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Cập nhật thương hiệu thành công');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->delete();

        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Xóa thương hiệu thành công');
    }
}