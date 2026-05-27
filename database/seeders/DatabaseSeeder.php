<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        $admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@smartstock.id',
            'password' => Hash::make('password'),
            'role'     => 'Admin',
        ]);

        $manager = User::create([
            'name'     => 'Manajer Gudang',
            'email'    => 'manager@smartstock.id',
            'password' => Hash::make('password'),
            'role'     => 'Manajer Gudang',
        ]);

        $staff = User::create([
            'name'     => 'Staf Gudang',
            'email'    => 'staf@smartstock.id',
            'password' => Hash::make('password'),
            'role'     => 'Staf Gudang',
        ]);

        User::create([
            'name'     => 'Viewer',
            'email'    => 'viewer@smartstock.id',
            'password' => Hash::make('password'),
            'role'     => 'Viewer',
        ]);

        // Warehouses – real GPS coordinates for each city
        $warehouses = [
            [
                'name'         => 'Gudang Jakarta Pusat',
                'city'         => 'Jakarta',
                'lat'          => -6.2088,
                'lng'          => 106.8456,
                'address'      => 'Jl. Gajah Mada No. 12, Jakarta Pusat',
                'phone'        => '021-5551234',
                'manager_name' => 'Andi Pratama',
            ],
            [
                'name'         => 'Gudang Surabaya',
                'city'         => 'Surabaya',
                'lat'          => -7.2575,
                'lng'          => 112.7521,
                'address'      => 'Jl. Raya Darmo No. 45, Surabaya',
                'phone'        => '031-5552345',
                'manager_name' => 'Siti Aminah',
            ],
            [
                'name'         => 'Gudang Bandung',
                'city'         => 'Bandung',
                'lat'          => -6.9175,
                'lng'          => 107.6191,
                'address'      => 'Jl. Asia Afrika No. 88, Bandung',
                'phone'        => '022-5553456',
                'manager_name' => 'Rudi Hartono',
            ],
            [
                'name'         => 'Gudang Medan',
                'city'         => 'Medan',
                'lat'          => 3.5952,
                'lng'          => 98.6722,
                'address'      => 'Jl. Pemuda No. 23, Medan',
                'phone'        => '061-5554567',
                'manager_name' => 'Nina Sari',
            ],
            [
                'name'         => 'Gudang Makassar',
                'city'         => 'Makassar',
                'lat'          => -5.1477,
                'lng'          => 119.4327,
                'address'      => 'Jl. Veteran Selatan No. 57, Makassar',
                'phone'        => '0411-5555678',
                'manager_name' => 'Hamid Usman',
            ],
        ];

        $warehouseModels = [];
        foreach ($warehouses as $data) {
            $warehouseModels[] = Warehouse::create($data);
        }

        // Categories
        $categories = [
            ['name' => 'Smartphone',  'description' => 'Perangkat telepon pintar', 'color' => '#3b82f6'],
            ['name' => 'Laptop',      'description' => 'Komputer jinjing',         'color' => '#8b5cf6'],
            ['name' => 'Aksesoris',   'description' => 'Aksesoris elektronik',     'color' => '#f59e0b'],
            ['name' => 'Tablet',      'description' => 'Perangkat tablet',         'color' => '#10b981'],
            ['name' => 'Komputer',    'description' => 'Desktop & komponen',       'color' => '#ef4444'],
        ];

        $categoryModels = [];
        foreach ($categories as $data) {
            $categoryModels[] = Category::create($data);
        }

        // Suppliers
        $suppliers = [
            [
                'name'           => 'PT Samsung Electronics Indonesia',
                'contact_person' => 'James Kim',
                'phone'          => '021-89001234',
                'email'          => 'supply@samsung.co.id',
                'address'        => 'Kawasan Industri EJIP, Bekasi',
                'city'           => 'Bekasi',
            ],
            [
                'name'           => 'PT Apple Indonesia',
                'contact_person' => 'Sarah Chen',
                'phone'          => '021-57992345',
                'email'          => 'b2b@apple.co.id',
                'address'        => 'Sudirman Central Business District, Jakarta',
                'city'           => 'Jakarta',
            ],
            [
                'name'           => 'PT Lenovo Technology Indonesia',
                'contact_person' => 'David Tan',
                'phone'          => '021-30033456',
                'email'          => 'business@lenovo.co.id',
                'address'        => 'Wisma Mulia, Jl. Jend. Gatot Subroto, Jakarta',
                'city'           => 'Jakarta',
            ],
        ];

        $supplierModels = [];
        foreach ($suppliers as $data) {
            $supplierModels[] = Supplier::create($data);
        }

        // Products
        $products = [
            [
                'category_id'       => $categoryModels[0]->id,
                'supplier_id'       => $supplierModels[0]->id,
                'name'              => 'Samsung Galaxy S24 Ultra',
                'sku'               => 'SAM-S24U-BLK',
                'description'       => 'Smartphone flagship Samsung dengan S-Pen dan kamera 200MP.',
                'price'             => 18999000,
                'minimum_threshold' => 10,
                'unit'              => 'unit',
            ],
            [
                'category_id'       => $categoryModels[0]->id,
                'supplier_id'       => $supplierModels[1]->id,
                'name'              => 'Apple iPhone 15 Pro Max',
                'sku'               => 'APL-IP15PM-256',
                'description'       => 'iPhone terbaru dengan chip A17 Pro dan kamera 48MP.',
                'price'             => 22999000,
                'minimum_threshold' => 8,
                'unit'              => 'unit',
            ],
            [
                'category_id'       => $categoryModels[1]->id,
                'supplier_id'       => $supplierModels[2]->id,
                'name'              => 'Lenovo ThinkPad X1 Carbon Gen 12',
                'sku'               => 'LEN-X1C12-I7',
                'description'       => 'Laptop bisnis ultrabook dengan Intel Core i7 dan layar 14 inci.',
                'price'             => 32500000,
                'minimum_threshold' => 5,
                'unit'              => 'unit',
            ],
            [
                'category_id'       => $categoryModels[1]->id,
                'supplier_id'       => $supplierModels[1]->id,
                'name'              => 'Apple MacBook Pro 14" M3 Pro',
                'sku'               => 'APL-MBP14-M3',
                'description'       => 'MacBook Pro dengan chip M3 Pro, performa tinggi untuk profesional.',
                'price'             => 39999000,
                'minimum_threshold' => 5,
                'unit'              => 'unit',
            ],
            [
                'category_id'       => $categoryModels[3]->id,
                'supplier_id'       => $supplierModels[1]->id,
                'name'              => 'Apple iPad Pro 12.9" M4',
                'sku'               => 'APL-IPP129-M4',
                'description'       => 'iPad Pro dengan layar OLED dan chip M4.',
                'price'             => 21999000,
                'minimum_threshold' => 6,
                'unit'              => 'unit',
            ],
            [
                'category_id'       => $categoryModels[0]->id,
                'supplier_id'       => $supplierModels[0]->id,
                'name'              => 'Samsung Galaxy Tab S9+',
                'sku'               => 'SAM-TABS9P-256',
                'description'       => 'Tablet Android premium dengan layar 12.4 inci AMOLED.',
                'price'             => 14999000,
                'minimum_threshold' => 8,
                'unit'              => 'unit',
            ],
            [
                'category_id'       => $categoryModels[2]->id,
                'supplier_id'       => $supplierModels[0]->id,
                'name'              => 'Samsung 65W Super Fast Charger',
                'sku'               => 'SAM-CHG65W-USB',
                'description'       => 'Charger cepat 65W dengan konektor USB-C.',
                'price'             => 599000,
                'minimum_threshold' => 20,
                'unit'              => 'pcs',
            ],
            [
                'category_id'       => $categoryModels[2]->id,
                'supplier_id'       => $supplierModels[1]->id,
                'name'              => 'Apple AirPods Pro Gen 2',
                'sku'               => 'APL-APP2-WHT',
                'description'       => 'True wireless earbuds dengan Active Noise Cancellation.',
                'price'             => 3999000,
                'minimum_threshold' => 15,
                'unit'              => 'unit',
            ],
            [
                'category_id'       => $categoryModels[4]->id,
                'supplier_id'       => $supplierModels[2]->id,
                'name'              => 'Lenovo IdeaCentre AIO 5i',
                'sku'               => 'LEN-AIO5I-27',
                'description'       => 'All-in-one desktop dengan layar 27 inci QHD.',
                'price'             => 18500000,
                'minimum_threshold' => 4,
                'unit'              => 'unit',
            ],
            [
                'category_id'       => $categoryModels[2]->id,
                'supplier_id'       => $supplierModels[2]->id,
                'name'              => 'Lenovo USB-C Hub 7-in-1',
                'sku'               => 'LEN-HUBC7-SLV',
                'description'       => 'Hub USB-C dengan 7 port koneksi termasuk HDMI 4K.',
                'price'             => 699000,
                'minimum_threshold' => 25,
                'unit'              => 'pcs',
            ],
        ];

        $productModels = [];
        foreach ($products as $data) {
            $productModels[] = Product::create($data);
        }

        // Initial stock per warehouse
        $stockData = [
            // [product_idx, warehouse_idx, quantity]
            [0, 0, 45], [0, 1, 28], [0, 2, 15], [0, 3, 8],  [0, 4, 12],
            [1, 0, 30], [1, 1, 20], [1, 2, 10], [1, 3, 5],  [1, 4, 7],
            [2, 0, 22], [2, 1, 15], [2, 2, 12], [2, 3, 6],  [2, 4, 9],
            [3, 0, 18], [3, 1, 10], [3, 2, 8],  [3, 3, 4],  [3, 4, 6],
            [4, 0, 25], [4, 1, 18], [4, 2, 11], [4, 3, 7],  [4, 4, 9],
            [5, 0, 35], [5, 1, 22], [5, 2, 16], [5, 3, 10], [5, 4, 13],
            [6, 0, 80], [6, 1, 60], [6, 2, 45], [6, 3, 30], [6, 4, 35],
            [7, 0, 50], [7, 1, 40], [7, 2, 28], [7, 3, 18], [7, 4, 22],
            [8, 0, 12], [8, 1, 8],  [8, 2, 6],  [8, 3, 3],  [8, 4, 5],
            [9, 0, 90], [9, 1, 75], [9, 2, 55], [9, 3, 40], [9, 4, 48],
        ];

        foreach ($stockData as [$pIdx, $wIdx, $qty]) {
            WarehouseStock::create([
                'product_id'   => $productModels[$pIdx]->id,
                'warehouse_id' => $warehouseModels[$wIdx]->id,
                'quantity'     => $qty,
            ]);
        }

        // Seed some sample transactions for dashboard charts
        $transactionTypes = ['Masuk', 'Keluar'];
        $refCounter = 1;

        for ($i = 0; $i < 30; $i++) {
            $type    = $transactionTypes[array_rand($transactionTypes)];
            $pIdx    = array_rand($productModels);
            $wIdx    = array_rand($warehouseModels);
            $qty     = rand(1, 20);

            InventoryTransaction::create([
                'product_id'       => $productModels[$pIdx]->id,
                'warehouse_id'     => $warehouseModels[$wIdx]->id,
                'supplier_id'      => $type === 'Masuk' ? $productModels[$pIdx]->supplier_id : null,
                'type'             => $type,
                'quantity'         => $qty,
                'operator_id'      => $staff->id,
                'status'           => 'completed',
                'reference_number' => 'TRX-' . str_pad($refCounter++, 6, '0', STR_PAD_LEFT),
                'notes'            => 'Data awal seeder',
                'created_at'       => now()->subDays(rand(0, 29)),
                'updated_at'       => now()->subDays(rand(0, 29)),
            ]);
        }
    }
}
