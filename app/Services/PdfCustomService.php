<?php

namespace App\Services;

use Imagick;
use Exception;
use setasign\Fpdi\Fpdi;
use App\Models\Document;
use Illuminate\Support\Str;
use App\Models\ApproverOrdinates;
use App\Interfaces\ResourceInterface;
use App\Interfaces\PdfCustomInterface;

class PdfCustomService implements PdfCustomInterface
{
    protected $fpdi;
    protected $resource_interface;
    private $leftMargin = 10;
    private $rightMargin = 10;
    private $topMargin = 15;
    private $usableWidth;



    public function __construct(Fpdi $fpdi,ResourceInterface $resource_interface)
    {
        $this->fpdi = $fpdi;
        $this->resource_interface = $resource_interface;
        $this->fpdi->AddPage('P', 'A4');
        $this->fpdi->SetFont('Arial', '', 9);
        $this->fpdi->SetMargins($this->leftMargin, $this->topMargin, $this->rightMargin);

        // A4 width = 210 mm
        $this->usableWidth = 210 - $this->leftMargin - $this->rightMargin;
    }
    /**
     * Get the total number of pages in a PDF file.
     *
     * @param string $filePath
     * @return int
     * composer require calcinai/php-imagick
     * composer require setasign/fpd
     */
    public function getPageCount(string $filePath)
    {
        // $pdf = new Fpdi();

        if (!file_exists($filePath)) {
            throw new Exception('PDF file not found.');
        }

        $pageCount = $this->fpdi->setSourceFile($filePath);
        return $pageCount;
    }

    /**
     * Convert a specific PDF page to an image and get its dimensions.
     *
     * @param string $filePath Path to the PDF file
     * @param int $pageNumber Page number to convert
     * @return array Image data, width, and height
     * @throws \Exception If the conversion fails
     */
    public function convertPdfPageToImage(string $filePath, int $pageNumber, string $outputDir)
    { //Passive Function
        if (!file_exists($filePath)) {
            throw new Exception('PDF file not found.');
        }
        $imagick = new Imagick();
        /**
         * Set desired resolution
        */
        $imagick->setResolution(300, 300);

        /**
         * Read specific page
         */
		$imagick->readImage($filePath.'['.($pageNumber).']');
        $width = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();

        /**
         * Convert to an image
         */
        $imagick->setImageFormat('jpeg');

        /**
         * PDF pages can contain transparency,
         * which sometimes renders as black in image formats like JPEG.
         * To fix this, set a white background color before converting.
         */

        $imagick->setImageAlphaChannel(\Imagick:: ALPHACHANNEL_REMOVE);

        /**
         * Flattening removes any transparency layers that might be causing issues:
         */

        $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        /**
         * To improve quality, set a higher resolution before reading the PDF page with Imagick.
         * A higher resolution will result in a clearer, higher-quality image but also a larger
         * file size.
         */

        $imagick->setImageCompressionQuality(100);

        /**
         * Optimize for web (optional)
        */

        $imagick->stripImage();
        /**
         * ! Error: Malformed UTF-8 characters, possibly incorrectly encoded
         * ! Error: When return as json
         * @param string $imageData Return Image
         * $imageData = $imagick->getImagesBlob();
         * return response($imagePath, 200) ->header('Content-Type', 'image/jpeg');
         */

        // Get image data as base64 Blob
        $imageData = base64_encode($imagick->getImagesBlob());
        /**
         * Return array insted of string
        */
        return [
            'image' => 'data:image/jpeg;base64,' . $imageData,
            'width' => $width,
            'height' => $height,
        ];
    }

     /**
     * Insert an image at specific coordinates on a PDF page.
     *
     * @param string $pdfPath Path to the original PDF file.
     * @param string $imagePath Path to the image file to insert.
     * @param float $x X-coordinate for the image.
     * @param float $y Y-coordinate for the image.
     * @param int $page Page number where the image will be inserted.
     * @return string Path to the newly generated PDF with the image.
     */
    public function insertImageAtCoordinates($pdfPath, $imagePath, $x, $y, $page = 1)
    {
        // Import the existing PDF page
        $pageCount = $this->fpdi->setSourceFile($pdfPath);

        //Read all page using page count
        for ($i=1; $i <= $pageCount; $i++) {
            $templateId = $this->fpdi->importPage($i);
            // Insert the image at specified coordinates
            $pdfDimensions = $this->fpdi->getTemplateSize($templateId);
            $w = $pdfDimensions['width'];
            $h = $pdfDimensions['height'];
            //Calculate the position of the Signature Image
            $x = $x * $w;
            $y = $y * $h;

            $orientation 	= 'P';
            $page_size 	= 'A4';
            if($w > $h){
                $orientation 	= 'L';
                /* A4 size is width 210 x height 297 mm */
                /* A3 size is width 297 x height 420 mm */
                if($w > 297){
                    $page_size 			= 'A3';
                }
            }

            // Add a page to the PDF
            $this->fpdi->AddPage($orientation,$page_size);
            $this->fpdi->useTemplate($templateId);

            // Add a image to the PDF
            $this->fpdi->Image($imagePath, $x, $y, 22, 0);
            $this->fpdi->SetFont('Arial', '', 5);

            // Generate a file path for the output PDF
            // $outputPath = storage_path('app/public/modified_pdf.pdf');
        }
        $this->fpdi->Output();
    }
    /**
     * insertMultipleImageAtCoordinates
     * @param mixed $data
     * @return void
     */
    public function insertMultipleImageAtCoordinates($data)
    {
        //Get Approver Ordinates
        $getDocumentWithApproverOrdinates =    $this->getDocumentWithApproverOrdinates($data);
        $pdfPath = $getDocumentWithApproverOrdinates[0]->filtered_document_name;
        $pdfPath = storage_path('app/public/edocs/'.$getDocumentWithApproverOrdinates[0]->id.'/'.$pdfPath);
        $arrApproverOrdinates = $getDocumentWithApproverOrdinates[0]->approver_ordinates;

        // Import the existing PDF page
        $pageCount = $this->fpdi->setSourceFile($pdfPath);

        //Read all page using page count
        for ($i=1; $i <= $pageCount; $i++) {
            // $currentPageNo = $i;
            $templateId = $this->fpdi->importPage($i);
            // Insert the image at specified coordinates
            $pdfDimensions = $this->fpdi->getTemplateSize($templateId);
            $page_size 	= 'A4';
            $orientation 	= 'P';
            // Add a page to the PDF

            $w = $pdfDimensions['width'];
            $h = $pdfDimensions['height'];
            if($w > $h){
                $orientation 	= 'L';
                /* A4 size is width 210 x height 297 mm */
                /* A3 size is width 297 x height 420 mm */
                if($w > 297){
                    $page_size 			= 'A3';
                }
            }
            $this->fpdi->AddPage($orientation,$page_size);
            $this->fpdi->useTemplate($templateId);

            //User Define
            foreach ($arrApproverOrdinates as $key => $valueApproverOrdinates) {
                $currentOrdinates = explode(' | ',$valueApproverOrdinates->ordinates);
                $arrPageNo = $valueApproverOrdinates->page_no;

                if($arrPageNo == $i){
                    $imagePath = storage_path('app/public/images/R152.png'); //TODO: get esignature based on emp_id save the path to the database also
                    //Calculate the position of the Signature Image
                    $x = $currentOrdinates[0] * $w;
                    $y = $currentOrdinates[1] * $h;
                    // Add a image to the PDF
                    $this->fpdi->Image($imagePath, $x, $y-2, 22, 0);
                }
            }
        }
        // View Output;
        $this->fpdi->Output();
    }
    private function getDocumentWithApproverOrdinates($documentId){
        $documentId = decrypt($documentId);
        $relations = [
            'approver_ordinates'
        ];
        $conditions = [
            'id' => $documentId,
        ];
        return $ApproverOrdinates = $this->resource_interface->readOnlyRelationsAndConditions(Document::class,[],$relations,$conditions);
    }
    public function generatePdfProductMaterialFPDF($data)
    {
        // return 'true';
        // Sample product data
        $products = [
            [
                "description" => "TR405-1040 Base & Cover Tray",
                "length"      => 360,
                "width"       => 175,
                "height"      => 40.9,
                "material"    => "APET",
                "thickness"   => 1.0,
                "material_w"  => 445,
                "prices" => [
                    [500,  "pcs", "$ 1.2689"],
                    [1000, "pcs", "$ 1.2195"],
                    [2000, "pcs", "$ 1.1886"],
                    [3000, "pcs", "$ 1.1618"],
                ]
            ],
            [
                "description" => "Cover Tray",
                "length"      => 100,
                "width"       => 100,
                "height"      => 100,
                "material"    => "Cover Tray",
                "thickness"   => 100,
                "material_w"  => 100,
                "prices" => [
                    [500,  "pcs", "$ 0.95"],
                    [1000, "pcs", "$ 0.92"],
                    [2000, "pcs", "$ 0.90"],
                    [3000, "pcs", "$ 0.88"],
                ]
            ]
        ];

        // Initialize PDF
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $usableWidth = 190;

        // Column widths
        $wDesc = $usableWidth * 0.30;
        $wSpecs = $usableWidth * 0.22;
        $wRawMat = $usableWidth * 0.22;
        $wLoopCols = $usableWidth * 0.26;
        $wMoq = $wLoopCols * 0.34;
        $wUom = $wLoopCols * 0.28;
        $wPrice = $wLoopCols * 0.38;

        // HEADER
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($wDesc, 7, "DESCRIPTION", 1, 0, 'C');
        $pdf->Cell($wSpecs, 7, "SPECS", 1, 0, 'C');
        $pdf->Cell($wRawMat, 7, "RAW MATERIAL", 1, 0, 'C');
        $pdf->Cell($wMoq, 7, "MOQ", 1, 0, 'C');
        $pdf->Cell($wUom, 7, "UOM", 1, 0, 'C');
        $pdf->Cell($wPrice, 7, "Price/Pc", 1, 1, 'C');

        // SUB-HEADERS
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell($wDesc, 7, "", 1);
        $pdf->Cell($wSpecs / 3, 7, "Length", 1, 0, 'C');
        $pdf->Cell($wSpecs / 3, 7, "Width", 1, 0, 'C');
        $pdf->Cell($wSpecs / 3, 7, "Height", 1, 0, 'C');

        $pdf->Cell($wRawMat / 3, 7, "Type", 1, 0, 'C');
        $pdf->Cell($wRawMat / 3, 7, "Thick", 1, 0, 'C');
        $pdf->Cell($wRawMat / 3, 7, "Width", 1, 0, 'C');

        $pdf->Cell($wMoq, 7, "MOQ", 1, 0, 'C');
        $pdf->Cell($wUom, 7, "UOM", 1, 0, 'C');
        $pdf->Cell($wPrice, 7, "Price/Pc", 1, 1, 'C');

        // PRODUCTS LOOP
        foreach ($products as $product) {
            $prices = $product["prices"];
            $firstPrice = $prices[0];

            $startX = $pdf->GetX();
            $startY = $pdf->GetY();

            // DESCRIPTION
            $pdf->MultiCell($wDesc, 6, $product["description"], 1);
            $rowHeight = $pdf->GetY() - $startY;

            $pdf->SetXY($startX + $wDesc, $startY);

            // SPECS
            $pdf->Cell($wSpecs / 3, $rowHeight, $product['length'], 1, 0, 'C');
            $pdf->Cell($wSpecs / 3, $rowHeight, $product['width'], 1, 0, 'C');
            $pdf->Cell($wSpecs / 3, $rowHeight, $product['height'], 1, 0, 'C');

            // RAW MATERIAL
            $pdf->Cell($wRawMat / 3, $rowHeight, $product['material'], 1, 0, 'C');
            $pdf->Cell($wRawMat / 3, $rowHeight, $product['thickness'], 1, 0, 'C');
            $pdf->Cell($wRawMat / 3, $rowHeight, $product['material_w'], 1, 0, 'C');

            // FIRST PRICE ROW
            $pdf->Cell($wMoq, $rowHeight, $firstPrice[0], 1, 0, 'C');
            $pdf->Cell($wUom, $rowHeight, $firstPrice[1], 1, 0, 'C');
            $pdf->Cell($wPrice, $rowHeight, $firstPrice[2], 1, 1, 'C');

            // REMAINING PRICE ROWS
            for ($i = 1; $i < count($prices); $i++) {
                $p = $prices[$i];
                $pdf->Cell($wDesc + $wSpecs + $wRawMat, 8, "", 1, 0);
                $pdf->Cell($wMoq, 8, $p[0], 1, 0, 'C');
                $pdf->Cell($wUom, 8, $p[1], 1, 0, 'C');
                $pdf->Cell($wPrice, 8, $p[2], 1, 1, 'C');
            }
        }

        // Output PDF to browser
        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf');
    }
    // public function generatePdfProductMaterial($data){

    // }

    /**
     * Public entry. $products is an array of product arrays.
     * Returns PDF binary string (you can stream or download it).
     */
    public function generatePdfProductMaterial(array $data)
    {
        // return $data;
        // === FONT SETTINGS ===
        $this->fpdi->SetFont('Arial', '', 10);

        // === HEADER DETAILS ===
        $this->fpdi->SetXY(10, 10);
        $this->fpdi->SetFont('Arial', 'B', 12);
        $this->fpdi->Cell(190, 6, "PMI-CN-2504-022", 0, 1, "L");

        $this->fpdi->SetFont('Arial', '', 10);
        $this->fpdi->Ln(2);

        $this->fpdi->Cell(100, 5, "To:           " . $data['to'], 0, 1);
        $this->fpdi->Cell(100, 5, "Attn.:        " . $data['attn'], 0, 1);
        $this->fpdi->Cell(100, 5, "CC:          " . $data['cc'], 0, 1);
        $this->fpdi->Cell(100, 5, "Subject:   " . $data['subject'], 0, 1);
        $this->fpdi->Cell(100, 5, "Date:        " . $data['date'], 0, 1);

        $this->fpdi->Ln(5);
        $this->fpdi->MultiCell(190, 5, "We are pleased to submit quotation for TR405-1040 and TR407-1040 tray:");

        // echo json_encode($data['descriptions']);
        // exit;
        // Table header once
        $products = [
            [
                "description" => ["TR405-1040 Base & Cover Tray", "Cover Tray"],
                "length"      => [360, 360],
                "width"       => [175, 175],
                "height"      => [40.9, 40.9],
                "material"    => ["APET", "APET"],
                "thickness"   => [1.0, 1.0],
                "material_w"  => [445, 445],
                "prices" => [
                    [500,  "pcs", "$ 1.2689"],
                    [1000, "pcs", "$ 1.2195"],
                    [2000, "pcs", "$ 1.1886"],
                    [3000, "pcs", "$ 1.1618"],
                ],
            ],
            [
                "description" => ["Cover Tray"],
                "length"      => [100],
                "width"       => [100],
                "height"      => [100],
                "material"    => ["Cover Tray"],
                "thickness"   => [100],
                "material_w"  => [100],
                "prices" => [
                    [500,  "pcs", "$ 0.95"],
                    [1000, "pcs", "$ 0.92"],
                    [2000, "pcs", "$ 0.90"],
                    [3000, "pcs", "$ 0.88"],
                ],
            ]
        ];
        // echo json_encode( $products);
        // echo json_encode( $data['descriptions'][3]);
        // exit;
        $ctrMaterial = 1;
        $descriptions = collect($data['descriptions'])->map(function ($item) {
            return [
                 "itemsId" =>    [$item['itemsId']],
                "itemNo" =>      [$item['itemNo']],
                "partCode" =>    [$item['partCode']],
                "description" => [$item['descriptionPartName']],
                "length"      => [$item['matSpecsLength']],
                "width"       => [$item['matSpecsWidth']],
                "height"      => [$item['matSpecsHeight']],
                "material"    => [$item['matRawType']],
                "thickness"   => [$item['matRawThickness']],
                "material_w"  => [$item['matRawWidth']],

                // Transform nested prices → [qty, "pcs", "$ 0.00"]
                "prices" => collect($item['classifications'])->map(function ($p) {
                    return [
                        $p['qty'],
                        "pcs",
                        "$ " . number_format($p['unitPrice'], 4)
                    ];
                })->values()->toArray(),
            ];
        })->values()->groupBy('itemNo')->toArray();
        // echo json_encode($descriptions[1]);

        // foreach ($descriptions as $key => $value) {
        //     echo json_encode($value);
        //     // echo json_encode($ctrMaterial);
        //     $ctrMaterial++;
        // }
        // exit;


        $ctrMaterialctrMaterial = 1;
        for ($indexMaterial=0; $indexMaterial < count($descriptions); $indexMaterial++) {
            // echo json_encode($descriptions[$ctrMaterial]);
            $this->buildProductTable($descriptions[$ctrMaterial]);
            $ctrMaterial++;
        }
        $this->fpdi->Ln(5);
        $this->fpdi->SetFont('Arial', 'B', 10);
        $this->fpdi->Cell(190, 5, "Terms and Conditions:", 0, 1);
        $this->fpdi->SetFont('Arial', '', 10);
        foreach ($data['terms'] as $i => $term) {
            $this->fpdi->Cell(190, 5, ($i + 1) . ". " . $term, 0, 1);
        }
        // ===== SIGNATORY SECTION =====
        $this->fpdi->Ln(10);
        $this->fpdi->SetFont('Arial', '', 10);
        $this->fpdi->Cell(190, 5, "For your information and acceptance.", 0, 1);

        $this->fpdi->Ln(10);
        $this->fpdi->Cell(90, 5, "Prepared by:", 0, 0);
        $this->fpdi->Cell(90, 5, "Checked by:", 0, 1);
        $this->fpdi->Ln(5);
        $this->fpdi->Cell(90, 5, $data['prepared_by'], 0, 0);
        $this->fpdi->Cell(90, 5, $data['checked_by'], 0, 1);

        $this->fpdi->Ln(10);
        $this->fpdi->Cell(90, 5, "Noted by:", 0, 1);
        $this->fpdi->Ln(5);
        $this->fpdi->Cell(90, 5, $data['noted_by'], 0, 1);

        $this->fpdi->Ln(10);
        $this->fpdi->Cell(90, 5, "Approved by:", 0, 1);
        $this->fpdi->Ln(5);
        $this->fpdi->Cell(90, 5, $data['noted_by'], 0, 0);
        $this->fpdi->Cell(90, 5, $data['checked_by'], 0, 1);


        return $this->fpdi->Output('S');

    }
    /**
     * Build table for many products (stacked vertically).
     */
    private function buildProductTable(array $products)
    {
        // Column width strategy (proportional)
        $wDesc     = $this->usableWidth * 0.30;  // DESCRIPTION block
        $wSpecs    = $this->usableWidth * 0.22;  // SPECS group total
        $wRawMat   = $this->usableWidth * 0.22;  // RAW MATERIAL group total
        $wLoopCols = $this->usableWidth * 0.26;  // MOQ/UOM/Price group total

        // Split group columns
        $wSpecsSub = $wSpecs / 3;            // Length / Width / Height
        $wRawSub   = $wRawMat / 3;           // Type / Thick / Width

        $wMoq   = $wLoopCols * 0.34;
        $wUom   = $wLoopCols * 0.28;
        $wPrice = $wLoopCols * 0.38;

        // Draw header (big grouped header + subheaders)
        $this->fpdi->SetFillColor(230, 230, 230);
        $this->fpdi->SetDrawColor(120);
        $this->fpdi->SetLineWidth(0.2);
        $this->headerCell($wDesc, 'DESCRIPTION');
        $this->headerCell($wSpecs, 'SPECS');
        $this->headerCell($wRawMat, 'RAW MATERIAL');
        $this->headerCell($wMoq, 'MOQ');
        $this->headerCell($wUom, 'UOM');
        $this->headerCell($wPrice, 'Price/Pc', true);

        // Sub-headers row
        $rowH = 7;
        $this->fpdi->SetFont('Arial', 'B', 9);
        $this->fpdi->Cell($wDesc, $rowH, '', 1, 0, 'C'); // top-left under description
        $this->fpdi->Cell($wSpecsSub, $rowH, 'Length', 1, 0, 'C');
        $this->fpdi->Cell($wSpecsSub, $rowH, 'Width', 1, 0, 'C');
        $this->fpdi->Cell($wSpecsSub, $rowH, 'Height', 1, 0, 'C');

        $this->fpdi->Cell($wRawSub, $rowH, 'Type', 1, 0, 'C');
        $this->fpdi->Cell($wRawSub, $rowH, 'Thick', 1, 0, 'C');
        $this->fpdi->Cell($wRawSub, $rowH, 'Width', 1, 0, 'C');

        $this->fpdi->Cell($wMoq,   $rowH, 'MOQ', 1, 0, 'C');
        $this->fpdi->Cell($wUom,   $rowH, 'UOM', 1, 0, 'C');
        $this->fpdi->Cell($wPrice, $rowH, 'Price/Pc', 1, 1, 'C');

        // Reset font for body
        $this->fpdi->SetFont('Arial', '', 9);

        // For each product draw merged left block + price rows
        foreach ($products as $key  => $product) {
            // return $products;
            // Normalize arrays: if values are scalars, wrap into array
            $descArr = is_array($product['description']) ? $product['description'] : [$product['description']];
            $lenArr  = is_array($product['length']) ? $product['length'] : [$product['length']];
            $widArr  = is_array($product['width']) ? $product['width'] : [$product['width']];
            $hgtArr  = is_array($product['height']) ? $product['height'] : [$product['height']];
            $matArr  = is_array($product['material']) ? $product['material'] : [$product['material']];
            $thkArr  = is_array($product['thickness']) ? $product['thickness'] : [$product['thickness']];
            $mwArr   = is_array($product['material_w']) ? $product['material_w'] : [$product['material_w']];
            $prices  = $product['prices'] ?? [];

            // Determine sub-rows (how many lines inside LEFT blocks)
            $subRows = max(
                count($descArr),
                count($lenArr),
                count($widArr),
                count($hgtArr),
                count($matArr),
                count($thkArr),
                count($mwArr)
            );

            // Price rows count
            $priceRows = max(1, count($prices));

            // Determine block height: must fit both subRows and priceRows
            $subRowHeight = 6;                 // height per sub-row in left blocks
            $minBlockHeight = $subRows * $subRowHeight;
            $priceRowHeight = 7;               // preferred per-price row height
            $minPriceBlockHeight = $priceRows * $priceRowHeight;

            // Final block height is max of both so row-span covers all prices and sub-lines
            $blockHeight = max($minBlockHeight, $minPriceBlockHeight);

            // Compute actual per-price-row height so price rows will fill the block exactly
            $actualPriceRowH = $blockHeight / $priceRows;

            // Save X/Y top-left of this product block
            $x = $this->fpdi->GetX();
            $y = $this->fpdi->GetY();

            // DRAW LEFT MERGED RECTANGLES (DESCRIPTION, SPECS, RAW MATERIAL)
            // We'll draw border boxes and then print the multi-lines inside.

            // DESCRIPTION box
            $this->fpdi->Rect($x, $y, $wDesc, $blockHeight);
            $this->drawRectText($x, $y, $wDesc, $blockHeight, $descArr, $subRowHeight);

            // SPECS box (we'll split it into 3 vertical subcolumns but treat as single rectangle)
            $sx = $x + $wDesc;
            $this->fpdi->Rect($sx, $y, $wSpecs, $blockHeight);
            // inside SPECS, we display each sub-row across 3 columns horizontally per sub-row
            // we must print subRows lines; for each line, print length/width/height in the three subcells
            $curY = $y;
            for ($r = 0; $r < $subRows; $r++) {
                $len = $lenArr[$r] ?? '';
                $wid = $widArr[$r] ?? '';
                $hgt = $hgtArr[$r] ?? '';
                $this->fpdi->SetXY($sx, $curY);
                $this->fpdi->Cell($wSpecsSub, $subRowHeight, $len, 0, 0, 'C');
                $this->fpdi->Cell($wSpecsSub, $subRowHeight, $wid, 0, 0, 'C');
                $this->fpdi->Cell($wSpecsSub, $subRowHeight, $hgt, 0, 0, 'C');
                $curY += $subRowHeight;
            }
            // draw vertical separators for SPECS subcols
            $this->fpdi->SetDrawColor(120);
            $this->fpdi->Line($sx + $wSpecsSub, $y, $sx + $wSpecsSub, $y + $blockHeight);
            $this->fpdi->Line($sx + 2*$wSpecsSub, $y, $sx + 2*$wSpecsSub, $y + $blockHeight);

            // RAW MATERIAL box
            $rx = $sx + $wSpecs;
            $this->fpdi->Rect($rx, $y, $wRawMat, $blockHeight);
            $curY = $y;
            for ($r = 0; $r < $subRows; $r++) {
                $mat = $matArr[$r] ?? '';
                $thk = $thkArr[$r] ?? '';
                $mw  = $mwArr[$r] ?? '';
                $this->fpdi->SetXY($rx, $curY);
                // $this->drawRectText($x, $y, $wDesc, $blockHeight, $matArr, $subRowHeight);
                $this->fpdi->Cell($wRawSub, $subRowHeight, $mat, 0, 0, 'C');
                $this->fpdi->Cell($wRawSub, $subRowHeight, $thk, 0, 0, 'C');
                $this->fpdi->Cell($wRawSub, $subRowHeight, $mw, 0, 0, 'C');
                $curY += $subRowHeight;
            }
            // draw vertical separators for RAW MATERIAL subcols
            $this->fpdi->Line($rx + $wRawSub, $y, $rx + $wRawSub, $y + $blockHeight);
            $this->fpdi->Line($rx + 2*$wRawSub, $y, $rx + 2*$wRawSub, $y + $blockHeight);

            // RIGHT: price area starting X
            $px = $rx + $wRawMat;

            // For each price row, draw small rectangles stacked top-to-bottom filling blockHeight
            $curPY = $y;
            for ($pr = 0; $pr < $priceRows; $pr++) {
                // MOQ cell
                $this->fpdi->Rect($px, $curPY, $wMoq, $actualPriceRowH);
                $this->fpdi->SetXY($px, $curPY);
                $moq = $prices[$pr][0] ?? '';
                $this->fpdi->Cell($wMoq, $actualPriceRowH, $moq, 0, 0, 'C');

                // UOM cell
                $this->fpdi->Rect($px + $wMoq, $curPY, $wUom, $actualPriceRowH);
                $this->fpdi->SetXY($px + $wMoq, $curPY);
                $uom = $prices[$pr][1] ?? '';
                $this->fpdi->Cell($wUom, $actualPriceRowH, $uom, 0, 0, 'C');

                // Price cell
                $this->fpdi->Rect($px + $wMoq + $wUom, $curPY, $wPrice, $actualPriceRowH);
                $this->fpdi->SetXY($px + $wMoq + $wUom, $curPY);
                $priceText = $prices[$pr][2] ?? '';
                $this->fpdi->Cell($wPrice, $actualPriceRowH, $priceText, 0, 0, 'C');

                $curPY += $actualPriceRowH;
            }

            // move cursor to below this block for next product
            $this->fpdi->SetY($y + $blockHeight + 2);
        }
    }
    /**
     * Helper: draw a header cell with fill and optional line break
     */
    private function headerCell(float $w, string $label, bool $endRow = false)
    {
        $this->fpdi->SetFillColor(230, 230, 230);
        $this->fpdi->SetFont('Arial', 'B', 9);
        $this->fpdi->Cell($w, 10, $label, 1, $endRow ? 1 : 0, 'C', true);
        $this->fpdi->SetFont('Arial', '', 9);
    }
    /**
     * Draw rectangle and print multiple lines inside (top-aligned)
     * $lines is array of strings printed top-to-bottom with $lineH per line.
     */
    private function drawRectText(float $x, float $y, float $w, float $h, array $lines, float $lineH = 6)
    {
        // Draw outer rect (already drawn by caller in many cases - safe to draw again)
        $this->fpdi->Rect($x, $y, $w, $h);

        // Print each line within rect (top aligned)
        $curY = $y + 1; // small padding
        $this->fpdi->SetXY($x + 2, $curY);
        foreach ($lines as $line) {
            // Prevent overflow - if next line would exceed box bottom, stop
            if ($curY + $lineH > $y + $h) {
                break;
            }
            $this->fpdi->MultiCell($w - 4, $lineH, $line, 0, 'L');
            $curY += $lineH;
            $this->fpdi->SetXY($x + 2, $curY);
        }
    }
}
