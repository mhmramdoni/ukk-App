<?php

namespace App\Exports;

use App\Models\saless;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class salesimport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithEvents, ShouldAutoSize
{
    /**
     * Ambil data berdasarkan role.
     */
    public function collection()
    {
        if (Auth::user()->role === 'employee') {
            return saless::with('customer', 'user', 'detail_sales')->orderBy('id', 'desc')->get();
        } else {
            return saless::with('customer', 'user', 'detail_sales')->orderBy('id', 'desc')->get();
        }
    }

    /**
     * Judul tab sheet.
     */
    public function title(): string
    {
        return 'Laporan Transaksi';
    }

    /**
     * Header kolom Excel.
     */
    public function headings(): array
    {
        return [
            [
                'LAPORAN TRANSAKSI', // Judul utama, nanti disetting merge
            ],
            [ // Headings kolom data
                'Nama Pembeli',
                'No HP Pembeli',
                'Point Pembeli',
                'Produk',
                'Total Harga',
                'Total Bayar',
                'Total Diskon Point',
                'Total Kembalian',
                'Tanggal Pembelian',
            ],
        ];
    }

    /**
     * Mapping data ke dalam format Excel.
     */
    public function map($item): array
    {
        return [
            optional($item->customer)->name ?? 'Bukan Member',
            optional($item->customer)->no_hp ?? '-',
            optional($item->customer)->point ?? 0,
            $item->detail_sales->map(function ($detail) {
                return optional($detail->product)->name
                    ? optional($detail->product)->name . ' (' . $detail->amount . ' x Rp' . number_format($detail->subtotal, 0, ',', '.') . ')'
                    : 'Produk tidak tersedia';
            })->implode(', '),
            number_format($item->detail_sales->sum('subtotal'), 0, ',', '.'),
            number_format($item->total_pay, 0, ',', '.'),
            number_format($item->total_price - (optional($item->customer)->point ?? 0), 0, ',', '.'),
            number_format($item->total_return, 0, ',', '.'),
            $item->created_at->format('d-m-Y H:i'),
        ];
    }

    /**
     * Event untuk memformat tampilan Excel.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Merge cell A1 sampai I1 untuk judul
                $event->sheet->mergeCells('A1:I1');
                // Styling judul
                $event->sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Styling header kolom (baris ke-2)
                $event->sheet->getStyle('A2:I2')->applyFromArray([
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
