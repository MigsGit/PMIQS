<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class InternalMachineSheet implements
WithEvents,
WithTitle,
ShouldAutoSize,
WithStrictNullComparison
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //
    }
    public function title(): string
    {
        return 'Weekly Summary';
    }
    public function registerEvents(): array
    {
        $styleBorderAll = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        return [
            AfterSheet::class => function (AfterSheet $event) use ($styleBorderAll){
                $sheet = $event->sheet;
                 // 🔹 Auto-size columns
                 foreach (range('A', 'K') as $col) {
                    $sheet->getDelegate()->getColumnDimension($col)->setAutoSize(true);
                }
                //🔹Merge header cells
                $sheet->mergeCells("A1:R1");
                $sheet->mergeCells("A1:B2");
                // 🔹 Set Custom Header
                // $sheet->setCellValue("M2", "Document Affected");//M-N
                // $sheet->setCellValue("M3", "Document Affected"); //Pinatanggal

                $sheet->setCellValue("A1", "TS IQC Performance"); //A-B
                $sheet->setCellValue("A2", "Section");
                $sheet->setCellValue("Q2", "Document Affected");//Q2-3
                $sheet->setCellValue("A3", "Product Line");
                $sheet->setCellValue("A4", "Device Name");
                $sheet->setCellValue("A5", "Customer");
                $sheet->setCellValue("A6", "Date of Application");
                //4M Category
                $sheet->setCellValue("A7", "4M Category");
                $sheet->setCellValue("J7", "Category"); //Category
                $sheet->setCellValue("J8", "Category"); //
            }
        ];
    }
}
