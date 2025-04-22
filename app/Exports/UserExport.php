<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UserExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithEvents, ShouldAutoSize
{
    /**
     * Ambil semua data user
     */
    public function collection()
    {
        return User::all();
    }

    /**
     * Judul sheet/tab Excel
     */
    public function title(): string
    {
        return 'Data User';
    }

    /**
     * Header Excel (judul utama dan kolom)
     */
    public function headings(): array
    {
        return [
            ['DATA USER'],
            [
                'No',
                'Nama',
                'Email',
                'Role',
                'Tanggal Dibuat',
            ],
        ];
    }

    /**
     * Mapping data per baris
     */
    public function map($user): array
    {
        static $index = 1;

        return [
            $index++,
            $user->name,
            $user->email,
            $user->role,
            $user->created_at->format('d-m-Y H:i'),
        ];
    }

    /**
     * Styling Excel
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Merge cell A1 sampai E1
                $event->sheet->mergeCells('A1:E1');

                // Judul utama
                $event->sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Header kolom (baris ke-2)
                $event->sheet->getStyle('A2:E2')->applyFromArray([
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
