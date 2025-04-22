<?php

namespace App\Exports;

use App\Models\products;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithEvents, ShouldAutoSize
{
    public function collection()
    {
        return products::orderBy('name')->get();
    }

    public function title(): string
    {
        return 'Data Produk';
    }

    public function headings(): array
    {
        return [
            ['DATA PRODUK'],
            [
                'No',
                'Nama Produk',
                'Harga',
                'Stok',
            ],
        ];
    }

    public function map($product): array
    {
        static $index = 1;

        return [
            $index++,
            $product->name,
            'Rp ' . number_format($product->price, 0, ',', '.'),
            $product->stock,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Merge judul utama
                $event->sheet->mergeCells('A1:D1');

                // Style untuk judul utama
                $event->sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Style untuk heading kolom
                $event->sheet->getStyle('A2:D2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];
    }
}
