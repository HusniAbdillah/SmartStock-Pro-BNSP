<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\ErrorLog;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessBatchImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        private readonly string $filePath,
        private readonly int $userId,
    ) {}

    public function handle(): void
    {
        $fullPath = Storage::disk('local')->path($this->filePath);

        if (!file_exists($fullPath)) {
            $this->logError("File import tidak ditemukan: {$this->filePath}");
            return;
        }

        $file   = fopen($fullPath, 'r');
        $header = fgetcsv($file);

        if (!$header) {
            fclose($file);
            $this->logError('File CSV kosong atau format tidak valid.');
            return;
        }

        // Normalize header
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        $chunk   = [];
        $row     = 0;
        $errors  = [];
        $success = 0;

        while (($line = fgetcsv($file)) !== false) {
            $row++;
            $data = array_combine($header, $line);

            // Validate required columns
            if (empty($data['name']) || empty($data['sku'])) {
                $errors[] = "Baris {$row}: kolom name/sku wajib diisi.";
                continue;
            }

            // Skip if SKU already exists
            if (Product::where('sku', trim($data['sku']))->exists()) {
                $errors[] = "Baris {$row}: SKU «{$data['sku']}» sudah ada, dilewati.";
                continue;
            }

            // Resolve category
            $categoryId = null;
            if (!empty($data['category_id']) && is_numeric($data['category_id'])) {
                $categoryId = (int) $data['category_id'];
            } elseif (!empty($data['category'])) {
                $cat = Category::firstOrCreate(
                    ['name' => trim($data['category'])],
                    ['color' => '#64748b']
                );
                $categoryId = $cat->id;
            }

            if (!$categoryId) {
                $errors[] = "Baris {$row}: kategori tidak valid, menggunakan kategori default.";
                $categoryId = Category::first()?->id ?? 1;
            }

            $chunk[] = [
                'category_id'       => $categoryId,
                'name'              => trim($data['name']),
                'sku'               => trim($data['sku']),
                'description'       => trim($data['description'] ?? ''),
                'price'             => is_numeric($data['price'] ?? 0) ? (float) $data['price'] : 0,
                'minimum_threshold' => is_numeric($data['minimum_threshold'] ?? 5) ? (int) $data['minimum_threshold'] : 5,
                'unit'              => trim($data['unit'] ?? 'unit'),
                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            // Insert in chunks of 50 for parallel efficiency
            if (count($chunk) >= 50) {
                DB::transaction(function () use ($chunk) {
                    DB::table('products')->insert($chunk);
                });
                $success += count($chunk);
                $chunk = [];
            }
        }

        // Insert remaining records
        if (!empty($chunk)) {
            DB::transaction(function () use ($chunk) {
                DB::table('products')->insert($chunk);
            });
            $success += count($chunk);
        }

        fclose($file);

        // Log result
        ErrorLog::create([
            'severity'   => empty($errors) ? 'info' : 'warning',
            'message'    => "Import batch selesai: {$success} produk berhasil diimpor." . (!empty($errors) ? ' ' . count($errors) . ' baris diabaikan.' : ''),
            'source'     => 'batch_import',
            'context'    => ['success' => $success, 'errors' => array_slice($errors, 0, 10)],
            'created_at' => now(),
        ]);

        // Clean up temp file
        Storage::disk('local')->delete($this->filePath);
    }

    public function failed(\Throwable $e): void
    {
        $this->logError("Job import gagal: {$e->getMessage()}", $e->getTraceAsString());
    }

    private function logError(string $message, ?string $trace = null): void
    {
        ErrorLog::create([
            'severity'    => 'critical',
            'message'     => $message,
            'stack_trace' => $trace,
            'source'      => 'batch_import',
            'created_at'  => now(),
        ]);
    }
}
