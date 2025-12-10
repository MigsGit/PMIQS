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

    // A4 Portrait Width = 210mm
    private $leftMargin  = 20;
    private $rightMargin = 20;
    private $usableWidth;
    public function __construct(Fpdi $fpdi,ResourceInterface $resource_interface) {
        $this->fpdi = $fpdi;
        $this->resource_interface = $resource_interface;


        $this->fpdi->AddPage("P", "A4");
        $this->fpdi->SetFont('Arial', '', 10);

        $this->usableWidth = 210 - $this->leftMargin - $this->rightMargin;
        $this->fpdi->SetMargins($this->leftMargin, 15, $this->rightMargin);
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
    public function generatePdfProductMaterial($data)
    {
        // return 'true';
        $newFpdi = new Fpdi();

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
        $arrDescriptions = $data['descriptions'];
        // echo json_encode($arrDescriptions);
        // exit;

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

        $test = $this->buildProductTable($products);
        // $ctrRawMat = 1;
        // for ($indexRawMat=0; $indexRawMat < count($arrDescriptions); $indexRawMat++) {
        //     $valDescriptions = $arrDescriptions[$ctrRawMat];
        //     $product = [
        //         "description" => $ctrRawMat,
        //         "length"      => 360,
        //         "width"       => 175,
        //         "height"      => 40.9,
        //         "material"    => "APET",
        //         "thickness"   => "1.0",
        //         "material_w"  => 445,
        //         "prices" => [
        //             [500,  "pcs", "$ 1.2689"],
        //             [1000, "pcs", "$ 1.2195"],
        //             [2000, "pcs", "$ 1.1886"],
        //             [3000, "pcs", "$ 1.1618"],
        //         ]
        //     ];
        //     $test = $this->buildRawMaterialTable($valDescriptions);
        //     // echo json_encode($test);
        //     $this->fpdi->Ln(6);
        //     $ctrRawMat++;

        // }
        // exit;

        // ===== TERMS AND CONDITIONS =====
        $this->fpdi->Ln(6);
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


        return $this->fpdi->Output("S"); // return as string
    }
    private function buildProductTable($products) {
        // PROPORTIONAL COLUMN WIDTHS (fit inside A4)
        $wDesc = $this->usableWidth * 0.30;
        $wSpecs = $this->usableWidth * 0.22;
        $wRawMat = $this->usableWidth * 0.22;
        $wLoopCols = $this->usableWidth * 0.26;
        $wMoq = $wLoopCols * 0.34;
        $wUom = $wLoopCols * 0.28;
        $wPrice = $wLoopCols * 0.38;

        // =============================== 1) HEADER ROW ===============================
        $this->headerCell($wDesc, "DESCRIPTION");
        $this->headerCell($wSpecs, "SPECS");
        $this->headerCell($wRawMat, "RAW MATERIAL");
        $this->headerCell($wMoq, "MOQ");
        $this->headerCell($wUom, "UOM");
        $this->headerCell($wPrice, "Price/Pc", true);

        // =============================== 2) SUB HEADERS ===============================
        $this->fpdi->Cell($wDesc, 7, "", 1);
        $this->fpdi->Cell($wSpecs / 3, 7, "Length", 1, 0, 'C');
        $this->fpdi->Cell($wSpecs / 3, 7, "Width", 1, 0, 'C');
        $this->fpdi->Cell($wSpecs / 3, 7, "Height", 1, 0, 'C');

        $this->fpdi->Cell($wRawMat / 3, 7, "Type", 1, 0, 'C');
        $this->fpdi->Cell($wRawMat / 3, 7, "Thick", 1, 0, 'C');
        $this->fpdi->Cell($wRawMat / 3, 7, "Width", 1, 0, 'C');

        $this->fpdi->Cell($wMoq, 7, "MOQ", 1, 0, 'C');
        $this->fpdi->Cell($wUom, 7, "UOM", 1, 0, 'C');
        $this->fpdi->Cell($wPrice, 7, "Price/Pc", 1, 1, 'C');

        // =============================== 3) LOOP THROUGH PRODUCTS ===============================
        foreach ($products as $product) {
            $prices = $product["prices"];
            $firstPrice = $prices[0];

            $startX = $this->fpdi->GetX();
            $startY = $this->fpdi->GetY();

            // DESCRIPTION
            $this->fpdi->MultiCell($wDesc, 6, $product["description"], 1);
            // $this->fpdi->Cell($wSpecs, $rowHeight, $product['length'], 1, 0, 'C');

            $rowHeight = $this->fpdi->GetY() - $startY;

            // Restore position after MultiCell
            $this->fpdi->SetXY($startX + $wDesc, $startY);

            // SPECS
            $this->fpdi->Cell($wSpecs / 3, $rowHeight, $product['length'], 1, 0, 'C');
            $this->fpdi->Cell($wSpecs / 3, $rowHeight, $product['width'], 1, 0, 'C');
            $this->fpdi->Cell($wSpecs / 3, $rowHeight, $product['height'], 1, 0, 'C');

            // RAW MATERIAL
            // $this->fpdi->Cell($wRawMat / 3, $rowHeight,Str::wordWrap($product['material'], 5, "\n", false), 1, 0, 'C');
            // $this->fpdi->Cell($wRawMat / 3, $rowHeight, wordwrap($product['material'], 5, "\n", true), 1, 0, 'C');
            $this->fpdi->Cell($wRawMat / 3, $rowHeight, wordwrap($product['material'], 5, "\n", true), 1, 0, 'C');
            // RAW MATERIAL: calculate max height for Material MultiCell
            $this->fpdi->Cell($wRawMat / 3, $rowHeight, $product['thickness'], 1, 0, 'C');
            $this->fpdi->Cell($wRawMat / 3, $rowHeight, $product['material_w'], 1, 0, 'C');

            // FIRST PRICE ROW
            $this->fpdi->Cell($wMoq, $rowHeight, $firstPrice[0], 1, 0, 'C');
            $this->fpdi->Cell($wUom, $rowHeight, $firstPrice[1], 1, 0, 'C');
            $this->fpdi->Cell($wPrice, $rowHeight, $firstPrice[2], 1, 1, 'C');

            // REMAINING PRICE ROWS
            for ($i = 1; $i < count($prices); $i++) {
                $p = $prices[$i];
                $this->fpdi->Cell($wDesc + $wSpecs + $wRawMat, 8, "", 2, 0);
                $this->fpdi->Cell($wMoq, 8, $p[0], 1, 0, 'C');
                $this->fpdi->Cell($wUom, 8, $p[1], 1, 0, 'C');
                $this->fpdi->Cell($wPrice, 8, $p[2], 1, 1, 'C');
            }
        }
    }
    /** ------------------------------------------------------------
     * Header Cell Helper
     * -------------------------------------------------------------- */
    private function headerCell($width, $label, $endRow = false)
    {
        $this->fpdi->SetFillColor(230, 230, 230);
        $this->fpdi->Cell($width, 8, $label, 1, $endRow ? 1 : 0, 'C', true);
        $this->fpdi->SetFillColor(255, 255, 255);
    }

    /** ------------------------------------------------------------
     * Build Product Table (Best Practice)
     * -------------------------------------------------------------- */
    private function buildRawMaterialTable($valDescriptions)
    {

        // PROPORTIONAL COLUMN WIDTHS (fit inside A4)
        $wDesc     = $this->usableWidth * 0.30;
        $wSpecs    = $this->usableWidth * 0.22;
        $wRawMat   = $this->usableWidth * 0.22;
        $wLoopCols = $this->usableWidth * 0.26;

        $wMoq      = $wLoopCols * 0.34;
        $wUom      = $wLoopCols * 0.28;
        $wPrice    = $wLoopCols * 0.38;

        /* ===============================
        1) HEADER ROW
        =============================== */
        $this->headerCell($wDesc,  "DESCRIPTION");
        $this->headerCell($wSpecs, "SPECS");
        $this->headerCell($wRawMat, "RAW MATERIAL");
        $this->headerCell($wMoq,   "MOQ");
        $this->headerCell($wUom,   "UOM");
        $this->headerCell($wPrice, "Price/Pc", true);

        /* ===============================
        2) SUB HEADERS
        =============================== */
        $this->fpdi->Cell($wDesc, 7, "", 1);

        $this->fpdi->Cell($wSpecs / 3, 7, "Length", 1, 0, 'C');
        $this->fpdi->Cell($wSpecs / 3, 7, "Width", 1, 0, 'C');
        $this->fpdi->Cell($wSpecs / 3, 7, "Height", 1, 0, 'C');

        $this->fpdi->Cell($wRawMat / 3, 7, "Type", 1, 0, 'C');
        $this->fpdi->Cell($wRawMat / 3, 7, "Thick", 1, 0, 'C');
        $this->fpdi->Cell($wRawMat / 3, 7, "Width", 1, 0, 'C');

        // Sub-header for first price row
        $this->fpdi->Cell($wMoq,   7, "", 1, 0, 'C');
        $this->fpdi->Cell($wUom,   7, "", 1, 0, 'C');
        $this->fpdi->Cell($wPrice, 7, "", 1, 1, 'C');

        /* ===============================
        3) MAIN ROW (aligned with first price)
        =============================== */

        // $prices = $valDescriptions["prices"];

        // // FIRST ROW OF THE PRICE TABLE (to align inside main row)
        // $firstPrice = $prices[0];



        // DESCRIPTION MultiCell
        for ($indexRawMatRow=0; $indexRawMatRow < count($valDescriptions); $indexRawMatRow++) {
            $startX = $this->fpdi->GetX();
            $startY = $this->fpdi->GetY();
            // echo json_encode($valDescriptions[$indexRawMatRow]);
            $rawMatRow = $valDescriptions[$indexRawMatRow];
            $this->fpdi->MultiCell($wDesc, 6, $rawMatRow['partCode'], 1);

            $rowHeight = $this->fpdi->GetY() - $startY;
            // // Restore after MultiCell
            // $this->fpdi->SetXY($startX + $wDesc, $startY);

            // // SPECS (3 columns)
            $this->fpdi->Cell($wSpecs / 3, $rowHeight, $rawMatRow['matSpecsLength'], 1, 0, 'C');
            // $this->fpdi->Cell($wSpecs / 3, $rowHeight, $rawMatRow['matSpecsWidth'], 1, 0, 'C');
            // $this->fpdi->Cell($wSpecs / 3, $rowHeight, $rawMatRow['matSpecsHeight'], 1, 0, 'C');

            // // RAW MATERIAL (3 columns)
            // $this->fpdi->Cell($wRawMat / 3, $rowHeight, $rawMatRow['matRawType'], 1, 0, 'C');
            // $this->fpdi->Cell($wRawMat / 3, $rowHeight, $rawMatRow['matRawThickness'], 1, 0, 'C');
            // $this->fpdi->Cell($wRawMat / 3, $rowHeight, $rawMatRow['matRawWidth'], 1, 0, 'C');

        }


        // // 🔥 FIRST PRICE ROW ALIGNED EXACTLY HERE
        // $this->fpdi->Cell($wMoq,   $rowHeight, $firstPrice[0], 1, 0, 'C');
        // $this->fpdi->Cell($wUom,   $rowHeight, $firstPrice[1], 1, 0, 'C');
        // $this->fpdi->Cell($wPrice, $rowHeight, $firstPrice[2], 1, 1, 'C');

        // /* ===============================
        // 4) REMAINING PRICE ROWS BELOW
        // =============================== */
        // for ($i = 1; $i < count($prices); $i++) {
        //     $p = $prices[$i];

        //     // Left side blank (DESC + SPECS + RAW MAT)
        //     $this->fpdi->Cell($wDesc + $wSpecs + $wRawMat, 8, "", 1, 0);

        //     // Loop cells
        //     $this->fpdi->Cell($wMoq,   8, $p[0], 1, 0, 'C');
        //     $this->fpdi->Cell($wUom,   8, $p[1], 1, 0, 'C');
        //     $this->fpdi->Cell($wPrice, 8, $p[2], 1, 1, 'C');
        // }
    }
    private function buildRawMaterialTableOne($product)
    {
        // PROPORTIONAL COLUMN WIDTHS (fit inside A4)
        $wDesc     = $this->usableWidth * 0.30;
        $wSpecs    = $this->usableWidth * 0.22;
        $wRawMat   = $this->usableWidth * 0.22;
        $wLoopCols = $this->usableWidth * 0.26;

        $wMoq      = $wLoopCols * 0.34;
        $wUom      = $wLoopCols * 0.28;
        $wPrice    = $wLoopCols * 0.38;

        /* ===============================
        1) HEADER ROW
        =============================== */
        $this->headerCell($wDesc,  "DESCRIPTION");
        $this->headerCell($wSpecs, "SPECS");
        $this->headerCell($wRawMat, "RAW MATERIAL");
        $this->headerCell($wMoq,   "MOQ");
        $this->headerCell($wUom,   "UOM");
        $this->headerCell($wPrice, "Price/Pc", true);

        /* ===============================
        2) SUB HEADERS
        =============================== */
        $this->fpdi->Cell($wDesc, 7, "", 1);

        $this->fpdi->Cell($wSpecs / 3, 7, "Length", 1, 0, 'C');
        $this->fpdi->Cell($wSpecs / 3, 7, "Width", 1, 0, 'C');
        $this->fpdi->Cell($wSpecs / 3, 7, "Height", 1, 0, 'C');

        $this->fpdi->Cell($wRawMat / 3, 7, "Type", 1, 0, 'C');
        $this->fpdi->Cell($wRawMat / 3, 7, "Thick", 1, 0, 'C');
        $this->fpdi->Cell($wRawMat / 3, 7, "Width", 1, 0, 'C');

        // Sub-header for first price row
        $this->fpdi->Cell($wMoq,   7, "", 1, 0, 'C');
        $this->fpdi->Cell($wUom,   7, "", 1, 0, 'C');
        $this->fpdi->Cell($wPrice, 7, "", 1, 1, 'C');

        /* ===============================
        3) MAIN ROW (aligned with first price)
        =============================== */

        $prices = $product["prices"];

        // FIRST ROW OF THE PRICE TABLE (to align inside main row)
        $firstPrice = $prices[0];

        $startX = $this->fpdi->GetX();
        $startY = $this->fpdi->GetY();

        // DESCRIPTION MultiCell
        $this->fpdi->MultiCell($wDesc, 6, $product["description"], 1);
        $rowHeight = $this->fpdi->GetY() - $startY;

        // Restore after MultiCell
        $this->fpdi->SetXY($startX + $wDesc, $startY);

        // SPECS (3 columns)
        $this->fpdi->Cell($wSpecs / 3, $rowHeight, $product['length'], 1, 0, 'C');
        $this->fpdi->Cell($wSpecs / 3, $rowHeight, $product['width'], 1, 0, 'C');
        $this->fpdi->Cell($wSpecs / 3, $rowHeight, $product['height'], 1, 0, 'C');

        // RAW MATERIAL (3 columns)
        $this->fpdi->Cell($wRawMat / 3, $rowHeight, $product['material'], 1, 0, 'C');
        $this->fpdi->Cell($wRawMat / 3, $rowHeight, $product['thickness'], 1, 0, 'C');
        $this->fpdi->Cell($wRawMat / 3, $rowHeight, $product['material_w'], 1, 0, 'C');

        // 🔥 FIRST PRICE ROW ALIGNED EXACTLY HERE
        $this->fpdi->Cell($wMoq,   $rowHeight, $firstPrice[0], 1, 0, 'C');
        $this->fpdi->Cell($wUom,   $rowHeight, $firstPrice[1], 1, 0, 'C');
        $this->fpdi->Cell($wPrice, $rowHeight, $firstPrice[2], 1, 1, 'C');

        /* ===============================
        4) REMAINING PRICE ROWS BELOW
        =============================== */
        for ($i = 1; $i < count($prices); $i++) {
            $p = $prices[$i];

            // Left side blank (DESC + SPECS + RAW MAT)
            $this->fpdi->Cell($wDesc + $wSpecs + $wRawMat, 8, "", 1, 0);

            // Loop cells
            $this->fpdi->Cell($wMoq,   8, $p[0], 1, 0, 'C');
            $this->fpdi->Cell($wUom,   8, $p[1], 1, 0, 'C');
            $this->fpdi->Cell($wPrice, 8, $p[2], 1, 1, 'C');
        }
    }
    private function buildSpecsTable($newFpdi, $item)
    {
        $this->fpdi->SetFont('Arial', 'B', 9);
        $this->fpdi->SetFillColor(230, 230, 230);

        // TABLE HEADER
        $this->fpdi->Cell(60, 6, "Description", 1, 0, "C", true);
        $this->fpdi->Cell(50, 6, "Specs", 1, 0, "C", true);
        $this->fpdi->Cell(40, 6, "Raw Material", 1, 0, "C", true);
        $this->fpdi->Cell(40, 6, "Price", 1, 1, "C", true);

        $this->fpdi->SetFont('Arial', '', 9);

        foreach ($item as $row) {
            $this->fpdi->Cell(60, 6, $row['description'], 1);
            $this->fpdi->Cell(50, 6, $row['specs'], 1);
            $this->fpdi->Cell(40, 6, $row['material'], 1);
            $this->fpdi->Cell(40, 6, $row['price'], 1, 1);
        }
    }
    private function descriptionClassificationTableProduct($newFpdi, $item)
    {
        $this->fpdi->SetFont('Arial', 'B', 9);
        $this->fpdi->SetFillColor(230, 230, 230);

        // TABLE HEADER
        $this->fpdi->Cell(60, 6, "Item#", 1, 0, "C", true);
        $this->fpdi->Cell(50, 6, "ProductCode/Type", 1, 0, "C", true);
        $this->fpdi->Cell(40, 6, "Description/ItemName", 1, 0, "C", true);
        $this->fpdi->Cell(40, 6, "Classification", 1, 1, "C", true);
        $this->fpdi->Cell(40, 6, "UOM", 1, 1, "C", true);
        $this->fpdi->SetFont('Arial', '', 9);

        foreach ($item as $row) {
            $this->fpdi->Cell(60, 6, $row['description'], 1);
            $this->fpdi->Cell(50, 6, $row['specs'], 1);
            $this->fpdi->Cell(40, 6, $row['material'], 1);
            $this->fpdi->Cell(40, 6, $row['price'], 1, 1);
            $this->fpdi->Cell(40, 6, $row['price'], 1, 1);
        }
    }
    private function descriptionClassificationTableRawMaterial($newFpdi, $item)
    {
        $this->fpdi->SetFont('Arial', 'B', 9);
        $this->fpdi->SetFillColor(230, 230, 230);

        // TABLE HEADER
        $this->fpdi->Cell(60, 6, "Description", 1, 0, "C", true);
        $this->fpdi->Cell(50, 6, "Specs", 1, 0, "C", true);
        $this->fpdi->Cell(40, 6, "Raw Material", 1, 0, "C", true);
        $this->fpdi->Cell(40, 6, "Price", 1, 1, "C", true);

        $this->fpdi->SetFont('Arial', '', 9);

        foreach ($item as $row) {
            $this->fpdi->Cell(60, 6, $row['description'], 1);
            $this->fpdi->Cell(50, 6, $row['specs'], 1);
            $this->fpdi->Cell(40, 6, $row['material'], 1);
            $this->fpdi->Cell(40, 6, $row['price'], 1, 1);
        }
    }

    /** ------------------------------------------------------------
     * Build Product Table (Best Practice)
     * -------------------------------------------------------------- */
    private function buildProductTableSpan($product)
    {
        // PROPORTIONAL COLUMN WIDTHS (fit inside A4)
        $wDesc     = $this->usableWidth * 0.30;
        $wSpecs    = $this->usableWidth * 0.22;
        $wRawMat   = $this->usableWidth * 0.22;
        $wLoopCols = $this->usableWidth * 0.26;

        $wMoq      = $wLoopCols * 0.34;
        $wUom      = $wLoopCols * 0.28;
        $wPrice    = $wLoopCols * 0.38;

        /* ===============================
        1) HEADER ROW
        =============================== */
        // $this->fpdi->Rect($startX, $startY, $w_desc, $merged_height);
        $this->headerCell($wDesc,  "DESCRIPTION");

        $this->headerCell($wSpecs, "SPECS");
        $this->headerCell($wRawMat, "RAW MATERIAL");
        $this->headerCell($wMoq,   "MOQ");
        $this->headerCell($wUom,   "UOM");
        $this->headerCell($wPrice, "Price/Pc", true);

        /* ===============================
        2) SUB HEADERS
        =============================== */
        $this->fpdi->Cell($wDesc, 7, "", 1);

        $this->fpdi->Cell($wSpecs / 3, 7, "Length", 1, 0, 'C');
        $this->fpdi->Cell($wSpecs / 3, 7, "Width", 1, 0, 'C');
        $this->fpdi->Cell($wSpecs / 3, 7, "Height", 1, 0, 'C');

        $this->fpdi->Cell($wRawMat / 3, 7, "Type", 1, 0, 'C');
        $this->fpdi->Cell($wRawMat / 3, 7, "Thick", 1, 0, 'C');
        $this->fpdi->Cell($wRawMat / 3, 7, "Width", 1, 0, 'C');

        // Sub-header for first price row
        $this->fpdi->Cell($wMoq,   7, "", 1, 0, 'C');
        $this->fpdi->Cell($wUom,   7, "", 1, 0, 'C');
        $this->fpdi->Cell($wPrice, 7, "", 1, 1, 'C');

        /* ===============================
        3) MAIN ROW (aligned with first price)
        =============================== */

        $prices = $product["prices"];

        // FIRST ROW OF THE PRICE TABLE (to align inside main row)
        $firstPrice = $prices[0];

        $startX = $this->fpdi->GetX();
        $startY = $this->fpdi->GetY();


        // DESCRIPTION MultiCell
        $this->fpdi->MultiCell($wDesc, 6, $product["description"], 2);
        // $rowHeight = $this->fpdi->GetY() - $startY;
        $rowHeight =36;
        $this->fpdi->Rect($startX, $startY,50+76, 36);

        // Restore after MultiCell
        $this->fpdi->SetXY($startX + $wDesc, $startY);

        // // SPECS (3 columns)
        // $this->fpdi->Cell($wSpecs / 3, $rowHeight, $product['length'], 1, 0, 'C');
        // $this->fpdi->Cell($wSpecs / 3, $rowHeight, $product['width'], 1, 0, 'C');
        // $this->fpdi->Cell($wSpecs / 3, $rowHeight, $product['height'], 1, 0, 'C');

        // // RAW MATERIAL (3 columns)
        // $this->fpdi->Cell($wRawMat / 3, $rowHeight, $product['material'], 1, 0, 'C');
        // $this->fpdi->Cell($wRawMat / 3, $rowHeight, $product['thickness'], 1, 0, 'C');
        // $this->fpdi->Cell($wRawMat / 3, $rowHeight, $product['material_w'], 1, 0, 'C');
        // SPECS (3 columns)
        $this->fpdi->Cell($wSpecs / 3, 36, $product['length'], 1, 0, 'C');
        $this->fpdi->Cell($wSpecs / 3, $rowHeight, $product['width'], 1, 0, 'C');
        $this->fpdi->Cell($wSpecs / 3, $rowHeight, $product['height'], 1, 0, 'C');

        // RAW MATERIAL (3 columns)
        $this->fpdi->Cell($wRawMat / 3, $rowHeight, $product['material'], 1, 0, 'C');
        $this->fpdi->Cell($wRawMat / 3, $rowHeight, $product['thickness'], 1, 0, 'C');
        $this->fpdi->Cell($wRawMat / 3, $rowHeight, $product['material_w'], 1, 0, 'C');

        // 🔥 FIRST PRICE ROW ALIGNED EXACTLY HERE
        $this->fpdi->Cell($wMoq,   $rowHeight, $firstPrice[0], 1, 0, 'C');
        $this->fpdi->Cell($wUom,   $rowHeight, $firstPrice[1], 1, 0, 'C');
        $this->fpdi->Cell($wPrice, $rowHeight, $firstPrice[2], 1, 1, 'C');

        /* ===============================
        4) REMAINING PRICE ROWS BELOW
        =============================== */
        for ($i = 1; $i < count($prices); $i++) {
            $p = $prices[$i];

            // Left side blank (DESC + SPECS + RAW MAT)
            $this->fpdi->Cell($wDesc + $wSpecs + $wRawMat, 8, "", 2, 0);

            // Loop cells
            $this->fpdi->Cell($wMoq,   8, $p[0], 1, 0, 'C');
            $this->fpdi->Cell($wUom,   8, $p[1], 1, 0, 'C');
            $this->fpdi->Cell($wPrice, 8, $p[2], 1, 1, 'C');
        }
    }
}
