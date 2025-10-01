<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Intervention\Image\Facades\Image;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class EcrExport implements WithEvents, WithTitle, ShouldAutoSize, WithStrictNullComparison
{

    protected $ecr;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function __construct($ecr) {
        $this->ecr = $ecr;
    }

    public function collection()
    {
        return $this->ecr;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'ECR Data';
    }

    public function insertEsignatureImageIntoSheet($imagePath, $coordinates, $width, $height, $sheet,$tempPathExt=null)
{
    // Get the full storage path of the image
    $imageStoragePath = Storage::path($imagePath.'.png');
    if( !file_exists($imageStoragePath) ){
        echo 'Signature not found: Please file a ticket to http://rapidx/iss_service_request/my_tickets';
        exit;
    }
    // Resize the image
    $image = Image::make($imageStoragePath)->resize($width, $height);
    $tempPath = storage_path("app/temp_resized_image_".$tempPathExt.".png");
    $image->save($tempPath);

    // Insert the image into the worksheet
    $drawing = new Drawing();
    $drawing->setName("Inserted Image");
    $drawing->setDescription("Inserted Image");
    $drawing->setPath($tempPath); // Path to the resized image
    $drawing->setCoordinates($coordinates); // Cell coordinates
    $drawing->setWorksheet($sheet); // Attach the image to the worksheet
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        date_default_timezone_set('Asia/Manila');
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $requestedByDeptCollection = $this->ecr['requestedByDeptCollection'];
                $ecrCollection = $this->ecr['ecrCollection'];
                $ecrApprovalsCollection = $ecrCollection->ecr_approvals;
                $ecrDetailsCollection = $ecrCollection->ecr_details;
                $sheet = $event->sheet->getDelegate();

                // === Alignment for input cells ===
                $sheet->getStyle("A1:AA100")->applyFromArray([
                    // 'font' => ['bold' => true, 'size' => 12,'name'=> 'Arial'],
                    'font' => ['size' => 12,'name'=> 'Arial'],
                    'alignment' => ['horizontal' => 'center'],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['argb' => Color::COLOR_WHITE], // White background
                        'wrapText' => true,
                    ],
                ]);
                // === Header Title ===
                $sheet->mergeCells('A2:H2');
                $sheet->setCellValue('A2', 'ENGINEERING CHANGE REQUEST');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 20,
                        'name' => 'Arial',
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => 'D3D3D3' ], // White background
                        'wrapText' => true,
                    ],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(25);
                // echo 'ECR NO.:'.' '. $ecrCollection->ecr_no;
                // exit;
                // === Section Headers Styling ===
                $sectionHeaders = [
                    'A3' => 'INFORMATION',
                    'F3' => 'ECR NO.:'.' '. $ecrCollection->ecr_no,
                    'A9' => 'DESCRIPTION OF CHANGE',
                    'A16' => 'REASON OF CHANGE',
                    'A25' => 'REQUESTED BY',
                    'A29' => 'REVIEWED BY / ENGG. SECTION HEAD',
                    'A40' => 'AGREED BY',
                ];
                $sectionHeadersEndRow = [
                    '3',
                    '3',
                    '9',
                    '16',
                    '25',
                    '29',
                    '40',
                ];
                $sectionHeadersCount = 0;
                foreach ($sectionHeaders as $cell => $value) {
                    $sheet->setCellValue($cell, $value);
                    $sheet->getStyle("{$cell}:H".$sectionHeadersEndRow[$sectionHeadersCount])->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => '0000FF'], // Blue text for headers
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'fill' => [
                            'fillType' => 'solid',
                            'startColor' => ['rgb' => 'D3D3D3' ], // White background
                            'wrapText' => true,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                    $sectionHeadersCount++;
                }

                // === Section Information Content ===
                $sectionContents = [
                    'A4' => 'Customer Name:',
                    'A5' => 'Part Name:',
                    'A6' => 'Product Line:',
                    'A7' => 'Section:',
                    'A8' => 'Customer Name',

                    'F5' => 'Part Number:',
                    'F6' => 'Device Name:',
                    'F7' => 'Customer EC No. (If any):',
                    'F8' => 'Date of Request:',
                ];

                foreach ($sectionContents as $cell => $value) {
                    $sheet->setCellValue($cell, $value);
                    $sheet->getStyle($cell)->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                }
                // === Approvers By Content ===
                $approverContents = [
                    'A26' => 'Department',
                    'B26' => 'Name',
                    'D26' => 'Title',
                    'E26' => 'Signature',
                    'F26' => 'Date',
                    'G26' => 'Remarks',
                    // === Reviewed By / Section Head Content ===
                    'C30' => 'APPROVED',
                    'F30' => 'NOT APPROVED',
                    'A36' => 'Department',
                    'B36' => 'Name',
                    'D36' => 'Title',
                    'E36' => 'Signature',
                    'F36' => 'Date',
                    'G36' => 'Remarks',
                    // === QA Content ===
                    'A41' => 'Department',
                    'B41' => 'Name',
                    'D41' => 'Title',
                    'E41' => 'Signature',
                    'F41' => 'Date',
                    'G41' => 'Remarks',

                ];

                foreach ($approverContents as $cell => $value) {
                    $sheet->setCellValue($cell, $value);
                    $sheet->getStyle($cell)->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                }
                $sheet->getStyle("B30")->applyFromArray([
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '000000' ], // White background
                        'wrapText' => true,
                    ],
                ]);
                //Ecr Collection Exist
                if(filled($ecrCollection)) {
                    $ecrCollectionContent = [
                        'B4' => $ecrCollection->customer_name,
                        'B5' => $ecrCollection->part_name,
                        'B6' => $ecrCollection->product_line,
                        'B7' => $ecrCollection->section,
                        'B8' => $ecrCollection->customer_name,

                        'G5' => $ecrCollection->part_no,
                        'G6' =>  $ecrCollection->device_name,
                        'G7' =>  $ecrCollection->customer_ec_no,
                        'G8' =>  $ecrCollection->date_of_request,
                    ];
                    foreach ($ecrCollectionContent as $cell => $value) {
                        $sheet->setCellValue($cell, $value);
                    }
                     //Ecr Collection Exist
                     /**
                        ecrApprovalsCollection
                        ecrDetailsCollection
                      */
                    if(filled($ecrDetailsCollection)) {
                        $startRowDocCollection = 10;
                        $startRowRocCollection = 17;
                        $startColumnEcrDetailsCollection = 'A';
                        foreach ($ecrDetailsCollection as $index => $value) {
                            $descriptionOfChange = $value->dropdown_master_detail_description_of_change->dropdown_masters_details;
                            $reasonOfChange = $value->dropdown_master_detail_reason_of_change->dropdown_masters_details;
                            $sheet->setCellValue("{$startColumnEcrDetailsCollection}{$startRowDocCollection}", $descriptionOfChange);
                            $startRowDocCollection++;

                            $sheet->setCellValue("{$startColumnEcrDetailsCollection}{$startRowRocCollection}", $reasonOfChange);
                            $startRowRocCollection++;
                        }
                    }


                    if(filled($ecrApprovalsCollection)) {
                        $startRowRequestedByApprovalsCollection = 27;
                        $startRowOtherApprovalsCollection = 37;
                        $startRowQaApprovalCollection = 42;
                        // $startColumnOtherApprovalsCollection = 'A';
                        foreach ($ecrApprovalsCollection as $index => $value) {
                            $approvalStatus = $value->approval_status ?? "";
                            $ecrApprover = $value->rapidx_user->name ?? "";
                            // date('Y-m-d',$value->rapidx_user->created_at) ?? "";
                            $approvedDate = Carbon::parse($value->created_at)->format('m-d-Y') ?? "";
                            $division = $requestedByDeptCollection[$index]['division'] ?? "";
                            $filteredSection = $requestedByDeptCollection[$index]['filteredSection'] ?? "";
                            $remarks = $requestedByDeptCollection[$index]['remarks'] ?? "N/A";
                            if (str_contains($approvalStatus, 'QA')) {
                                $sheet->setCellValue("A{$startRowQaApprovalCollection}", $division);
                                $sheet->setCellValue("B{$startRowQaApprovalCollection}", $ecrApprover);
                                $sheet->setCellValue("D{$startRowQaApprovalCollection}", $approvalStatus);
                                // $sheet->setCellValue("E{$startRowQaApprovalCollection}", 'Signature');
                                $sheet->setCellValue("F{$startRowQaApprovalCollection}", $approvedDate);
                                $sheet->setCellValue("G{$startRowQaApprovalCollection}", $remarks);
                                  // === E-signature Images
                                $imageEsigPath = 'public/e_signatures/';
                                $imageEsigWithEmpNumberPath = $imageEsigPath.$value->rapidx_user->employee_number;
                                $this->insertEsignatureImageIntoSheet(
                                    $imageEsigWithEmpNumberPath,
                                    "E".$startRowQaApprovalCollection,
                                    50,
                                    50,
                                    $sheet,
                                    'ecr_qa'.$index
                                );
                                $startRowQaApprovalCollection++;
                            }else{
                                if (str_contains($approvalStatus, 'OTRB')) {
                                    $sheet->setCellValue("A{$startRowRequestedByApprovalsCollection}", $division);
                                    $sheet->setCellValue("B{$startRowRequestedByApprovalsCollection}", $ecrApprover);
                                    $sheet->setCellValue("D{$startRowRequestedByApprovalsCollection}", $approvalStatus);
                                    $sheet->setCellValue("F{$startRowRequestedByApprovalsCollection}", $approvedDate);
                                    $sheet->setCellValue("G{$startRowOtherApprovalsCollection}", $remarks);
                                    // === Insert e-signature
                                    $imageEsigPath = 'public/e_signatures/';
                                    $imageEsigWithEmpNumberPath = $imageEsigPath.$value->rapidx_user->employee_number;
                                    $this->insertEsignatureImageIntoSheet(
                                        $imageEsigWithEmpNumberPath,
                                        "E".$startRowRequestedByApprovalsCollection,
                                        50,
                                        50,
                                        $sheet,
                                        'ecr_requestedby'.$index
                                    );
                                    $startRowRequestedByApprovalsCollection++;
                                }
                                if ( !str_contains($approvalStatus, 'OTRB')) {
                                    $sheet->setCellValue("A{$startRowOtherApprovalsCollection}", $division);
                                    $sheet->setCellValue("B{$startRowOtherApprovalsCollection}", $ecrApprover);
                                    $sheet->setCellValue("D{$startRowOtherApprovalsCollection}", $approvalStatus);
                                    // $sheet->setCellValue("E{$startRowOtherApprovalsCollection}", 'Signature');
                                    $sheet->setCellValue("F{$startRowOtherApprovalsCollection}", $approvedDate);
                                    $sheet->setCellValue("G{$startRowOtherApprovalsCollection}", $remarks);
                                      // === Insert e-signature
                                      $imageEsigPath = 'public/e_signatures/';
                                      $imageEsigWithEmpNumberPath = $imageEsigPath.$value->rapidx_user->employee_number;
                                      $this->insertEsignatureImageIntoSheet(
                                          $imageEsigWithEmpNumberPath,
                                          "E".$startRowOtherApprovalsCollection,
                                          50,
                                          50,
                                          $sheet,
                                          'ecr_engg'.$index
                                      );
                                    $startRowOtherApprovalsCollection++;
                                }
                            }
                        }
                    }
                }

                // === Specific Merged Cells ===
                $mergeCells = [
                    'A3:E3',
                    'F3:H3',
                    'A9:H9',
                    'A16:H16',
                    'A25:H25',
                    'A29:H29',
                    'A40:H40',

                    'B26:C26',
                    'B27:C27',
                    'B28:C28',

                    'G26:H26',
                    'G27:H27',
                    'G28:H28',
                    'G28:H28',

                    'B36:C36',
                    'B37:C37',
                    'B38:C38',
                    'B39:C39',

                    'G36:H36',
                    'G37:H37',
                    'G38:H38',
                    'G39:H39',

                    'B41:C41',
                    'B42:C42',
                    'B43:C43',
                    'B44:C44',

                    'G41:H41',
                    'G42:H42',
                    'G43:H43',
                    'G44:H44',
                ];

                foreach ($mergeCells as $range) {
                    $sheet->mergeCells($range);
                }

                // === Column Widths ===
                $columnWidths = [
                    'A' => 0,
                    'C' => 10,
                    'D' => 10,
                    'E' => 10,
                    'F' => 30,
                    'G' => 30,
                ];
                foreach ($columnWidths as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }

                // ===Row Heights for form look ===
                $customRowHeights = [
                    4 => 20,
                    9 => 20,
                    16 => 20,
                    25 => 20,
                    29 => 20,
                    40 => 20,
                ];
                foreach ($customRowHeights as $row => $height) {
                    $sheet->getRowDimension($row)->setRowHeight($height);
                }


                // Set border for range
                // echo 'true';
                // exit;
                // === Apply borders to specific cells ===
                $allThinBorder = [
                    "A26:H26",
                    "C36:H36",
                    "D41:H41",
                    'A26:H28',
                    'A36:H39',
                    'A41:H44',
                ];
                foreach ($allThinBorder as $key => $allThinBorderValue) {
                    $sheet->getStyle($allThinBorderValue)
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);
                }
                // === THIN BORDERS
                $rightThinBorder = [
                    "A5:A8",
                    "E5:E8",
                    "F5:F8",
                ];


                $bottomThinBorder = [
                    "A4:H4",
                ];

                foreach ($rightThinBorder as $key => $rightThinBorderValue) {
                    $sheet->getStyle($rightThinBorderValue)
                    ->applyFromArray([
                        'borders' => [
                            'right' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);
                }
                foreach ($bottomThinBorder as $key => $bottomThinBorderValue) {
                    $sheet->getStyle($bottomThinBorderValue)
                    ->applyFromArray([
                        'borders' => [
                            'bottom' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);
                }

                //THICK BORDERS
                $rightThickBorder = [
                    "H2:H44",
                ];
                $leftThickBorder = [
                    "A2:A44",
                ];
                $topThickBorder = [
                    "A2:H2",
                    "A45:H45",
                ];
                foreach ($rightThickBorder as $key => $rightThickBorderValue) {
                    $sheet->getStyle($rightThickBorderValue)
                    ->applyFromArray([
                        'borders' => [
                            'right' => [
                                'borderStyle' => Border::BORDER_THICK,
                            ],
                        ],
                    ]);
                }
                foreach ($leftThickBorder as $key => $leftThickBorderValue) {
                    $sheet->getStyle($leftThickBorderValue)
                    ->applyFromArray([
                        'borders' => [
                            'left' => [
                                'borderStyle' => Border::BORDER_THICK,
                            ],
                        ],
                    ]);
                }
                foreach ($topThickBorder as $key => $topThickBorderValue) {
                    $sheet->getStyle($topThickBorderValue)
                    ->applyFromArray([
                        'borders' => [
                            'top' => [
                                'borderStyle' => Border::BORDER_THICK,
                            ],
                        ],
                    ]);
                }

            },
        ];
    }
}
