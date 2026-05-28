<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Notifications\LowStockNotification;
use App\Notifications\ReportReadyNotification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $allUsers      = User::where('is_active', true)->get();
        $stockRecipients = $allUsers->whereIn('role', ['Admin', 'Manajer Gudang']);

        if ($allUsers->isEmpty()) {
            $this->command->warn('Tidak ada user aktif. Jalankan DatabaseSeeder terlebih dahulu.');
            return;
        }

        $products   = Product::all();
        $warehouses = Warehouse::where('is_active', true)->get();

        if ($products->isEmpty() || $warehouses->isEmpty()) {
            $this->command->warn('Produk atau gudang kosong. Jalankan DatabaseSeeder terlebih dahulu.');
            return;
        }

        // Hapus notifikasi lama agar tidak duplikat
        foreach ($allUsers as $user) {
            $user->notifications()->delete();
        }

        // ── 1. LowStockNotification — hanya Admin & Manajer Gudang ────────────
        // Stok kritis (habis) — karena dua role ini yang bertanggung jawab atas alert stok
        foreach ($stockRecipients as $user) {
            $user->notify(new LowStockNotification(
                product:      $products->get(0),
                currentStock: 0,
                warehouseId:  $warehouses->get(0)->id,
                severity:     'critical',
            ));
            $user->notify(new LowStockNotification(
                product:      $products->get(2),
                currentStock: 0,
                warehouseId:  $warehouses->get(1)->id,
                severity:     'critical',
            ));

            // Stok menipis (warning)
            $user->notify(new LowStockNotification(
                product:      $products->get(1),
                currentStock: 3,
                warehouseId:  $warehouses->get(0)->id,
                severity:     'warning',
            ));
            $user->notify(new LowStockNotification(
                product:      $products->get(3),
                currentStock: 2,
                warehouseId:  $warehouses->last()->id,
                severity:     'warning',
            ));

            $this->command->line("  [Admin/Manajer] Stok alert → {$user->name}");
        }

        // ── 2. ReportReadyNotification — semua role bisa generate laporan ─────
        // Route laporan hanya dilindungi 'auth' biasa, semua user bisa akses.
        foreach ($allUsers as $user) {

            // Laporan sudah selesai (sudah dibaca — historis)
            $user->notify(new ReportReadyNotification(
                filename:   'laporan_inventaris_' . now()->subDay()->format('Ymd_') . $user->id . '.pdf',
                fileSizeKb: rand(90, 150),
            ));
            $user->notifications()
                ->where('type', ReportReadyNotification::class)
                ->latest()
                ->first()
                ?->update(['read_at' => now()->subHours(rand(1, 5))]);

            // Laporan baru selesai (belum dibaca)
            $user->notify(new ReportReadyNotification(
                filename:   'laporan_inventaris_' . now()->format('Ymd_His') . '_' . $user->id . '.pdf',
                fileSizeKb: rand(90, 150),
            ));

            $this->command->line("  [Semua Role]    Laporan siap → {$user->name} ({$user->role})");
        }

        $total = $allUsers->count() * 2                   // 2 ReportReady per user
               + $stockRecipients->count() * 4;           // 4 stock alerts per admin/manajer

        $this->command->info("NotificationSeeder selesai — {$total} notifikasi dibuat.");
    }
}
