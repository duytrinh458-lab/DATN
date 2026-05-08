<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;

class BrandApiController extends Controller
{
    // 📌 API: GET LIST BRAND
    public function index()
    {
        $brands = Brand::orderBy('id', 'desc')->get();

        return response()->json([
            'status' => true,
            'data' => $brands
        ]);
    }
}