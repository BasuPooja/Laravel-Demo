<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles,
    WithDrawings
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ProductsExport implements 
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles,
    WithDrawings
{
    protected $products;

    public function __construct($products)
    {
        $this->products = $products;
    }

    public function collection()
    {
        return $this->products;
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Price', 'Description', 'Image'];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->price,
            $product->description,
            '' // image column placeholder
        ];
    }

    public function drawings()
    {
        $drawings = [];

        foreach ($this->products as $index => $product) {

            if ($product->image && Storage::disk('public')->exists($product->image)) {

                $drawing = new Drawing();
                $drawing->setName('Product Image');
                $drawing->setDescription('Product Image');
                $drawing->setPath(storage_path('app/public/' . $product->image));
                $drawing->setHeight(60);

                // E column = 5th column
                $drawing->setCoordinates('E' . ($index + 2));

                $drawings[] = $drawing;
            }
        }

        return $drawings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // header bold
        ];
    }
}