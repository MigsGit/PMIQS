<?php

namespace App\Exports\Sheets;
use Carbon\Carbon;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;


class ChangeControlManagementSheet implements
FromArray,
WithEvents
{

    protected $ecrsCategoryDetailsCollection;

    public function __construct($ecrsCategoryDetailsCollection)
    {
        $this->ecrsCategoryDetailsCollection = $ecrsCategoryDetailsCollection;
    }
    public function array(): array
    {
        return [[]];
    }
    /**
 * Inserts an image into the Excel sheet.
 *
 * @param string $imagePath Path to the image in storage.
 * @param string $coordinates Cell coordinates where the image will be placed.
 * @param int $width Width to resize the image.
 * @param int $height Height to resize the image.
 * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet Worksheet object.
 */
    public function insertEsignatureImageIntoSheet($imagePath, $coordinates, $width, $height, $sheet,$tempPathExt=null)
    {
        // Get the full storage path of the image
        $imageStoragePath = Storage::path($imagePath.'.png');

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
    public function registerEvents(): array
    {
        $ecrsDetails = $this->ecrsCategoryDetailsCollection['ecrDetails'];
        $pmiApprovalCollection = collect($ecrsDetails->pmi_approvals)->groupBy('approval_status')->toArray();
        $categoryDetails = $this->ecrsCategoryDetailsCollection['detailsByCategory'];
        return [
            AfterSheet::class => function (AfterSheet $event) use($ecrsDetails,$categoryDetails,$pmiApprovalCollection) {
                $sheet = $event->sheet->getDelegate();
                 // =========================================== //

                // === Column Widths
                foreach (range('A', 'L') as $col) {
                    $sheet->getColumnDimension($col)->setWidth(9.45);
                }

                // === Apply Styles to all cells used
                $sheet->getStyle('A59:G59')->applyFromArray([
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'wrapText' => true,
                    ],
                ]);
                // === Apply Styles to all cells used
                // $sheet->getStyle('A1:G40')->applyFromArray([
                //     'borders' => [
                //         'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                //     ],
                //     'alignment' => [
                //         'vertical' => Alignment::VERTICAL_CENTER,
                //         'horizontal' => Alignment::HORIZONTAL_LEFT,
                //         'wrapText' => true,
                //     ],
                // ]);


                // === Bold for header
                $sheet->getStyle('A1:A3')->getFont()->setBold(true);

                // Optional Row Heights
                for ($i = 1; $i <= 70; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(16.50);
                }
                for ($j = 59; $j <= 59; $j++) {
                    $sheet->getRowDimension($j)->setRowHeight( 33.5);
                }
                // === HEADER
                $sheet->setCellValue('A1', 'PRICON MICROELECTRONICS, INC.');
                $sheet->setCellValue('A2', 'OPERATIONS DIVISION');
                $sheet->setCellValue('C3', 'CHANGE CONTROL APPLICATION REPORT');
                $sheet->setCellValue('K1', 'PPS-101-018');
                $sheet->setCellValue('J4', 'Control Number');
                $sheet->setCellValue('J5', $ecrsDetails->ecr_no);
                // === SECTION INFO
                $sheet->setCellValue('A6', 'SECTION NAME');
                $sheet->setCellValue('A7', 'PRODUCT LINE');
                $sheet->setCellValue('A8', 'DEVICE NAME');
                $sheet->setCellValue('A9', 'PART NAME');
                $sheet->setCellValue('A10', 'PART CODE');
                $sheet->setCellValue('A11', 'CUSTOMER');
                $sheet->setCellValue('A12', 'DATE OF APPLICATION');
                $sectionCol = "C";
                $startSectionRow = "6";
                // === SECTION DATA
                $section = [
                    $ecrsDetails->section,
                    $ecrsDetails->product_line,
                    $ecrsDetails->device_name,
                    $ecrsDetails->part_name,
                    $ecrsDetails->part_no,
                    $ecrsDetails->customer_name,
                    $ecrsDetails->date_of_request,
                ];
                foreach ($section as $index => $label) {
                    $sheet->setCellValue($sectionCol . ($startSectionRow + $index), $label);
                }
                // === 4M CHANGE & DOCUMENTS
                $sheet->setCellValue('A14', '4M Change / 1E');
                $categoryCol = "B";
                $categoryRow = "14";
                // === SECTION DATA
                $isCategory = $ecrsDetails->category ?? "";
                $category = [
                    $isCategory === "Man" ? '☑ Man' :'☐ Man',
                    $isCategory === "Machine" ? '☑ Machine/Tools' :'☐ Machine/Tools',
                    $isCategory === "Material" ? '☑ Material' :'☐ Material',
                    $isCategory === "Method" ? '☑ Method' :'☐ Method',
                    $isCategory === "Environment" ? '☑ Environment' :'☐ Environment',
                ];
                for ($i=0; $i < count($category); $i++) {
                    $sheet->setCellValue($categoryCol. $categoryRow, $category[$i]); $categoryCol++;
                }
                $sheet->setCellValue('G6', 'Document Affected');

                // ======= Insert Before and After Image ========
                // Retrieve the image path
                $filteredDocumentNameBefore = explode(' | ',$categoryDetails->filtered_document_name_before);
                $storageImageDirBefore = 'public/'.$categoryDetails->file_path.'/'.$ecrsDetails->id.'/before/';
                if(file_exists($storageImageDirBefore) ){
                    $startBeforeImageCol = "A";
                    $startBeforeImageRow = "22";
                    foreach ($filteredDocumentNameBefore as $key => $valueBefore) {
                        $imagePathBefore[]= Storage::path($storageImageDirBefore.$valueBefore);
                    }
                    foreach ($imagePathBefore as $key => $imagePathBeforeValue) {
                            // Resize the image (optional, requires Intervention Image package)
                            $image = Image::make($imagePathBeforeValue)->resize(150, 300); // Resize to 300x300 pixels
                            $tempPath = storage_path("app/temp_resized_image_$key.jpg");
                            $image->save($tempPath);

                            // Calculate the cell coordinates dynamically
                            $currentRow = $startBeforeImageRow + ($key*1); // Move down 5 rows for each image

                            // Merge cells to accommodate the image
                            $endColumn = chr(ord($startBeforeImageCol) + 2); // Merge 3 columns (e.g., A, B, C)
                            // $sheet->mergeCells("$startBeforeImageCol$currentRow:$endColumn" . ($currentRow + 1));

                            // Dynamically adjust column widths and row heights
                            $imageWidth = $image->width();
                            $imageHeight = $image->height();

                            $columnWidth = $imageWidth / 9.5; // Approximation for column width
                            // $sheet->getColumnDimension($startBeforeImageCol)->setWidth($columnWidth);
                            // $sheet->getColumnDimension(chr(ord($startBeforeImageCol) + 1))->setWidth($columnWidth);
                            // $sheet->getColumnDimension($endColumn)->setWidth($columnWidth);

                            $rowHeight = $imageHeight / 1.5; // Approximation for row height
                            $sheet->getRowDimension($currentRow)->setRowHeight($rowHeight);
                            $sheet->getRowDimension($currentRow + 1)->setRowHeight($rowHeight);

                            // Insert the image into the merged cells
                            $drawing = new Drawing();
                            $drawing->setName("Image $key");
                            $drawing->setDescription("Image $key");
                            $drawing->setPath($tempPath); // Path to the resized image
                            $drawing->setCoordinates("$startBeforeImageCol$currentRow"); // Place the image at the top-left of the merged cells
                            $drawing->setWorksheet($sheet); // Attach the image to the worksheet
                    }
                }



                $filteredDocumentNameAfter = explode(' | ',$categoryDetails->filtered_document_name_after);
                $storageImageDirAfter = 'public/'.$categoryDetails->file_path.'/'.$ecrsDetails->id.'/after/';
                if(file_exists($storageImageDirBefore) ){
                    $startAfterImageCol = "D";
                    $startAfterImageRow = "22";
                    foreach ($filteredDocumentNameAfter as $index => $valueAfter) {
                        $imagePathAfter[]= Storage::path($storageImageDirAfter.$valueAfter);
                    }

                    foreach ($imagePathAfter as $index => $imagePathAfterValue) {
                            // Resize the image (optional, requires Intervention Image package)
                            $image = Image::make($imagePathAfterValue)->resize(150, 300); // Resize to 300x300 pixels
                            $tempPath = storage_path("app/temp_resized_image_after_$index.jpg");
                            $image->save($tempPath);

                            // Calculate the cell coordinates dynamically
                            $currentRow = $startAfterImageRow + ($index*1); // Move down 5 rows for each image

                            // Merge cells to accommodate the image
                            $endColumn = chr(ord($startAfterImageCol) + 2); // Merge 3 columns (e.g., A, B, C)
                            // $sheet->mergeCells("$startAfterImageCol$currentRow:$endColumn" . ($currentRow + 1));

                            // Dynamically adjust column widths and row heights
                            $imageWidth = $image->width();
                            $imageHeight = $image->height();

                            $columnWidth = $imageWidth / 10.5; // Approximation for column width
                            // $sheet->getColumnDimension($startAfterImageCol)->setWidth($columnWidth);
                            // $sheet->getColumnDimension(chr(ord($startAfterImageCol) + 1))->setWidth($columnWidth);
                            // $sheet->getColumnDimension($endColumn)->setWidth($columnWidth);

                            $rowHeight = $imageHeight / 1.5; // Approximation for row height
                            $sheet->getRowDimension($currentRow)->setRowHeight($rowHeight);
                            $sheet->getRowDimension($currentRow + 1)->setRowHeight($rowHeight);

                            // Insert the image into the merged cells
                            $drawing = new Drawing();
                            $drawing->setName("Image $index");
                            $drawing->setDescription("Image $index");
                            $drawing->setPath($tempPath); // Path to the resized image
                            $drawing->setCoordinates("$startAfterImageCol$currentRow"); // Place the image at the top-left of the merged cells
                            $drawing->setWorksheet($sheet); // Attach the image to the worksheet
                    }
                }

                // === Document Type
                $docTypes = [
                    '☐ QC Process Flow Chart',
                    '☐ Packaging Specification',
                    '☐ Part/Product Specification',
                    '☐ Assembly Drawing',
                    '☐ SG / Assembly Manual',
                ];
                $docTypesCol = "G";
                $docTypesRow = 8;
                $sheet->setCellValue($docTypesCol.$docTypesRow, '☐ Others (pls. specify)');
                foreach ($docTypes as $index => $label) {
                   $sheet->setCellValue($docTypesCol . ($docTypesRow + $index), $label);
                }

                // === Target Date and Attachment
                $sheet->setCellValue('G14', 'Target date of implementation:');
                $sheet->setCellValue('G15', 'With attachment:');
                $sheet->setCellValue('J15', '☐ Yes');
                $sheet->setCellValue('K15', '☐ No');
                $sheet->setCellValue('G16', 'Title of attachment:');
                $sheet->setCellValue('G19', 'Actual Sample Attached:');
                $sheet->setCellValue('J19', '☐ Yes');
                $sheet->setCellValue('K19', 'Qty: ______ pcs.');
                $sheet->setCellValue('J20', '☐ No');

                // === BEFORE/AFTER
                $sheet->setCellValue('A21', 'BEFORE');
                $sheet->setCellValue('D21', 'AFTER');
                $sheet->setCellValue('G21', 'REASON FOR APPLICATION');
                $sheet->setCellValue('G26', 'Prepared by:');

                $imageEsigPath = 'public/e_signatures/';
                // === Insert thre E-Signature Prepared By
                $this->insertEsignatureImageIntoSheet(
                    $imageEsigPath.$ecrsDetails->rapidx_user_created_by->employee_number,
                    "H26",
                    50,
                    50,
                    $sheet,
                    'prepared_by'
                );
                $sheet->setCellValue('H27', $ecrsDetails->rapidx_user_created_by->name);

                // exit();
                $sheet->setCellValue('J26', 'Checked by:');
                $sheet->setCellValue('A29', '4M / 1E CHANGE ASSESSMENT');
                // === 4M Assessment
                $rowsEffects = [
                    'Effect on Man (By Production)',
                    'Effect on Machine/Tools',
                    'Effect on Method/Environment',
                    'Effect on Materials',
                    'Line QC Remarks',
                    'PMI Approval',
                ];
                $startRowsEffects = 30;
                foreach ($rowsEffects as $i => $label) {
                    $sheet->setCellValue("A" . $startRowsEffects, $label);
                    // $sheet->setCellValue("D" . ($start + $i), 'Assessed by:');
                    // $sheet->setCellValue("F" . ($start + $i), 'Checked by: Section Head');
                    $startRowsEffects+=4;
                }
                $rowsAssessedby = [
                    'Assessed by',
                    'Assessed by',
                    'Assessed by',
                    'Assessed by',
                    'Assessed by',
                ];
                $startRowsAssessedby= 30;
                foreach ($rowsAssessedby as $i => $label) {
                    $sheet->setCellValue("I" . $startRowsAssessedby, $label);
                    $startRowsAssessedby+=4;
                }
                $rowsCheckedby = [
                    'Checked by',
                    'Checked by',
                    'Checked by',
                    'Checked by',
                    'Checked by',
                ];
                $startRowsCheckedby= 32;
                foreach ($rowsCheckedby as $i => $label) {
                    $sheet->setCellValue("I" . $startRowsCheckedby, $label);
                    $startRowsCheckedby+=4;
                }
                $rowsSectionHead = [
                    'Section Head',
                    'Section Head',
                    'Section Head',
                    'Section Head',
                    'Section Head',
                ];
                $startRowsSectionHead= 33;
                foreach ($rowsSectionHead as $i => $label) {
                    $sheet->setCellValue("K" . $startRowsSectionHead, $label);
                    $startRowsSectionHead+=4;
                }

                // === Approval Section
                $sheet->setCellValue('A50', 'PMI Approval');
                $sheet->setCellValue('B53', 'QC Head');
                $sheet->setCellValue('E53', 'Operations Head');
                $sheet->setCellValue('H53', 'QAD Head');
                // === Approval Data
                $startExtQcCol = "B";
                foreach ($pmiApprovalCollection['EXQC'] as $key => $extenalQcValue) {
                    $this->insertEsignatureImageIntoSheet(
                        $imageEsigPath.$extenalQcValue['rapidx_user']['employee_number'],
                        $startExtQcCol."51",
                        50,
                        50,
                        $sheet,
                        'qc_head'.$key
                    );
                    $sheet->setCellValue($startExtQcCol.'52', $extenalQcValue['rapidx_user']['name']);
                    $startExtQcCol++; //Adjust the Column
                }
                // === YEC Approval Section
                $sheet->setCellValue('A56', 'YEC Approval?');
                $sheet->setCellValue('C56', '☐ Need');
                $sheet->setCellValue('C58', '☐ No Need');
                $sheet->setCellValue('I55', 'Final Disposition:');
                $sheet->setCellValue('J57', '☐ Accept');
                $sheet->setCellValue('J58', '☐ Reject');
                $sheet->setCellValue('I59', 'REMARKS:');


                // === NOTE Column
                $sheet->setCellValue('A59', '**Note: If  YEC approval is necessary, PMI shall implement 4M change after the receipt of  YECs  Process
                    Change Application approval sheet.
                    If no need YEC approval, PMI can implement the  4M change immediately with PMI heads approval
                ');
                // === Conditional Section
                $sheet->setCellValue('A62', 'USE THIS PORTION IF DISPOSITION IS ACCEPTED WITH CONDITION');
                $sheet->setCellValue('A63', 'Action/s Required');
                $sheet->setCellValue('C63', 'Target Date');
                $sheet->setCellValue('E63', 'In-Charge');
                $sheet->setCellValue('G63', 'Result');
                $sheet->setCellValue('I68', 'QAD SIGNATURE');


                // === Specific Merged Cells ===
                $mergeCells = [
                    'J4:L4',
                    'J5:L5',
                    'A1:F1',
                    'A2:F2',
                    'C3:I4',
                    'K1:L1',
                    'J4:L4',
                    'G6:L6',

                    'A21:C21',
                    'D21:F21',
                    'G21:L21',
                    'A29:L29',
                    'A59:G59',

                    'A62:H62',
                    'A63:B64',
                    'C63:D64',
                    'E63:F64',
                    'G63:H64',
                ];

                foreach ($mergeCells as $range) {
                    $sheet->mergeCells($range);
                }

                //Style
                $arrCenterColumn = [
                    'C3',
                    'G6',
                    'J4',
                    'J5',
                    'J5',
                    'A62',
                    'A63',
                    'C63',
                    'E63',
                    'G63',
                ];
                foreach ($arrCenterColumn as $centerColumn) {
                    $sheet->getStyle($centerColumn)->applyFromArray([
                        'alignment' => [
                            'horizontal' => 'center',
                            'vertical' => 'center'
                        ],
                    ]);
                }




                $arrOutlineThin = [
                    'J4:L4',
                    'J5:L5',

                    'A6:F13',
                    'G6:L13',

                    'A14:F20',
                    'G14:L20',

                    'A21:C28',
                    'D21:F28',
                    'G21:L28',

                    'A29:L29',

                    //APPROVAL
                    'A30:L33',
                    'A34:L37',
                    'A38:L41',
                    'A42:L45',
                    'A46:L49',
                    'A50:L54',

                    //YEC APPROVAL
                    'A55:H61',
                    'I55:L58',
                    // REMARKS / FINAL DISPO
                    'A62:H62',
                    'A63:L69',

                    //ACTION TABLE
                    'A63:H64',
                    'A63:B69',
                    'C63:D69',
                    'E63:F69',
                    'G63:H69',
                    //QA
                    'I68:L69',
                ];
                foreach ($arrOutlineThin as $outlineThin) {
                    $sheet->getStyle($outlineThin)
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);
                }

                $sheet->getStyle('A1:L69')
                ->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THICK,
                        ],
                    ],
                ]);
            }
        ];
    }
}
