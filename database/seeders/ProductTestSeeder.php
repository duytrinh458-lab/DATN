<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;

class ProductTestSeeder extends Seeder
{
    public function run()
    {
        // 1. Tạo 1 sản phẩm UAV mẫu
        $product = Product::create([
            'category_id' => 1, 
            'name' => 'Vanguard Sentinel X1',
            'sku' => 'VG-SENTINEL-01',
            'description' => 'Dòng drone trinh sát cao cấp với camera 8K và thời gian bay vượt trội.',
            'original_price' => 50000000,
            'sale_price' => 45000000,
            'stock' => 10,
            'flight_time' => 45,
            'max_altitude' => 5000,
            'camera_mp' => 48,
            'status' => 'active'
        ]);

        // 2. Thêm 3-4 ảnh để test cái Slider lướt ảnh kiểu Shopee
        // Duy nhớ để ảnh vào thư mục public/uploads/products/ nhé
        ProductImage::create(['product_id' => $product->id, 'image_url' => 'uploads/products/uav-1.jpg', 'position' => 1]);
        ProductImage::create(['product_id' => $product->id, 'image_url' => 'uploads/products/uav-2.jpg', 'position' => 2]);
        ProductImage::create(['product_id' => $product->id, 'image_url' => 'uploads/products/uav-3.jpg', 'position' => 3]);
    }
}