<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductsImport implements ToCollection, WithHeadingRow
{
    protected $filePath;
    protected $extension;

    public function __construct($file)
    {
        $this->filePath = $file->getPathname();
        $this->extension = strtolower($file->getClientOriginalExtension());
    }

    public function collection(Collection $rows)
    {
        $imagesByRow = [];

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Handle Embedded Images (Only for Excel)
        |--------------------------------------------------------------------------
        */
        if (in_array($this->extension, ['xlsx', 'xls'])) {

            $spreadsheet = IOFactory::load($this->filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            foreach ($worksheet->getDrawingCollection() as $drawing) {

                if ($drawing instanceof Drawing) {

                    $coordinates = $drawing->getCoordinates();
                    preg_match('/\d+/', $coordinates, $matches);
                    $rowNumber = $matches[0];

                    $imageContents = file_get_contents($drawing->getPath());
                    $ext = pathinfo($drawing->getPath(), PATHINFO_EXTENSION);

                    if (!$ext) {
                        $ext = 'jpg';
                    }

                    $imageName = 'products/' . uniqid() . '.' . $ext;

                    Storage::disk('public')->put($imageName, $imageContents);

                    $imagesByRow[$rowNumber] = $imageName;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ Insert Products
        |--------------------------------------------------------------------------
        */
        foreach ($rows as $index => $row) {

            $excelRowNumber = $index + 2; // because of heading row
            $imageName = null;

            /*
            |--------------------------------------------------------------------------
            | A. Embedded Image (Excel Only)
            |--------------------------------------------------------------------------
            */
            if (isset($imagesByRow[$excelRowNumber])) {
                $imageName = $imagesByRow[$excelRowNumber];
            }

            /*
            |--------------------------------------------------------------------------
            | B. Image URL (Excel + CSV)
            |--------------------------------------------------------------------------
            */
           elseif (!empty($row['image'])) {

                $imageValue = trim($row['image']);

                // ✅ If it is a valid URL
                if (filter_var($imageValue, FILTER_VALIDATE_URL)) {

                    try {

                        /*
                        |--------------------------------------------------------------------------
                        | 1️⃣ If Image From Your Own Local Storage
                        |--------------------------------------------------------------------------
                        */
                        if (str_contains($imageValue, '127.0.0.1') || str_contains($imageValue, 'localhost')) {

                            $relativePath = str_replace(
                                url('/storage') . '/',
                                '',
                                $imageValue
                            );

                            if (Storage::disk('public')->exists($relativePath)) {

                                $ext = pathinfo($relativePath, PATHINFO_EXTENSION);
                                if (!$ext) $ext = 'jpg';

                                $imageName = 'products/' . uniqid() . '.' . $ext;

                                Storage::disk('public')->put(
                                    $imageName,
                                    Storage::disk('public')->get($relativePath)
                                );
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | 2️⃣ External Internet Image
                        |--------------------------------------------------------------------------
                        */
                        else {

                            $response = Http::timeout(20)->get($imageValue);

                            if ($response->successful()) {

                                $ext = pathinfo(parse_url($imageValue, PHP_URL_PATH), PATHINFO_EXTENSION);
                                if (!$ext) $ext = 'jpg';

                                $imageName = 'products/' . uniqid() . '.' . $ext;

                                Storage::disk('public')->put($imageName, $response->body());
                            }
                        }

                    } catch (\Exception $e) {
                        Log::error('Image download failed: ' . $e->getMessage());
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | 3️⃣ If Already Stored Path
                |--------------------------------------------------------------------------
                */
                else {
                    $imageName = $imageValue;
                }
            }

            Product::create([
                'name' => $row['name'] ?? null,
                'price' => $row['price'] ?? 0,
                'description' => $row['description'] ?? null,
                'image' => $imageName,
            ]);
        }
    }
}