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
        $descriptions = $data['descriptions'];

        $ctrMaterial = 1;
        if($data['category'] === "PRO"){
            for ($indexMaterial=0; $indexMaterial < count($descriptions); $indexMaterial++) {
                $this->buildProductTable($descriptions[$ctrMaterial]);
                $ctrMaterial++;
                $this->fpdi->Ln(3);
            }
        }
        if($data['category'] === "RM"){
            for ($indexMaterial=0; $indexMaterial < count($descriptions); $indexMaterial++) {
                $this->buildRawMatTable($descriptions[$ctrMaterial]);
                $ctrMaterial++;
                $this->fpdi->Ln(3);
            }
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
        // $this->fpdi->Ln(5);
        // $imagePath ='../RapidX_E-Signature/R152.png';

        // //TODO: get esignature based on emp_id save the path to the database also
        // Add images for prepared_by and checked_by
        $preparedByImagePath = '../RapidX_E-Signature/'.$data['prepared_by_emp_no'].'.png'; // Replace with actual path
        $checkedByImagePath = '../RapidX_E-Signature/'.$data['checked_by_emp_no'].'.png'; // Replace with actual path

        $this->addSignatureImage($preparedByImagePath, 10, $this->fpdi->GetY(), 30, 10); // Adjust X, Y, Width, Height
        $this->addSignatureImage($checkedByImagePath, 100, $this->fpdi->GetY(), 30, 10); // Adjust X, Y, Width, Height

        $this->fpdi->Ln(10);
        $this->fpdi->Cell(90, 5, $data['prepared_by'], 0, 0);
        $this->fpdi->Cell(90, 5, $data['checked_by'], 0, 1);

        $this->fpdi->Ln(10);
        $this->fpdi->Cell(90, 5, "Noted by:", 0, 1);
        // $this->fpdi->Ln(5);

        // Add image for noted_by
        $notedByImagePath = '../RapidX_E-Signature/'.$data['noted_by_emp_no'].'.png'; // Replace with actual path
        $this->addSignatureImage($notedByImagePath, 10, $this->fpdi->GetY(), 30, 10);

        $this->fpdi->Ln(10);
        $this->fpdi->Cell(90, 5, $data['noted_by'], 0, 1);

        $this->fpdi->Ln(10);
        $this->fpdi->Cell(90, 5, "Approved by:", 0, 1);
        // $this->fpdi->Ln(5);


        // Add image for approved_by
        $approvedByImagePath = '../RapidX_E-Signature'.$data['appoved_by1_emp_no'].'.png'; // Replace with actual path
        $approvedByImagePath2 = '../RapidX_E-Signature/'.$data['appoved_by2_emp_no'].'.png'; // Replace with actual path
        $this->addSignatureImage($approvedByImagePath, 10, $this->fpdi->GetY(), 30, 10);
        $this->addSignatureImage($approvedByImagePath2, 100, $this->fpdi->GetY(), 30, 10);

        $this->fpdi->Ln(10);
        $this->fpdi->Cell(90, 5, $data['approved_by1'], 0, 0);
        $this->fpdi->Cell(90, 5, $data['approved_by2'], 0, 1);
        // echo json_encode($data);
        // exit;

        return $this->fpdi->Output('S');

    }
    private function addSignatureImage($imagePath, $x, $y, $width, $height)
{
    if (file_exists($imagePath)) {
        $this->fpdi->Image($imagePath, $x, $y, $width, $height);
    } else {
        $this->fpdi->SetXY($x, $y);
        $this->fpdi->Cell($width, $height, 'No Signature', 1, 0, 'C');
    }
}
    private function buildRawMatTable(array $products){
        // Column width strategy (proportional)
        $wPartCode = $this->usableWidth * 0.10; // new Part Code column
        $wDesc     = $this->usableWidth * 0.15; // DESCRIPTION block
        $wSpecs    = $this->usableWidth * 0.18; // SPECS group total
        $wRawMat   = $this->usableWidth * 0.22; // RAW MATERIAL group total
        $wLoopCols = $this->usableWidth * 0.40; // MOQ/UOM/Price group total

        // Split group columns
        $wSpecsSub = $wSpecs / 3; // Length / Width / Height
        $wRawSub   = $wRawMat / 3; // Type / Thick / Width

        $wClassification = $wLoopCols * 0.30;
        $wMoq   = $wLoopCols * 0.12;
        $wUom   = $wLoopCols * 0.12;
        $wPrice = $wLoopCols * 0.20;

        // Draw header
        $this->fpdi->SetFillColor(230, 230, 230);
        $this->fpdi->SetDrawColor(120);
        $this->fpdi->SetLineWidth(0.2);

        $this->headerCell($wPartCode, 'Part Code');
        $this->headerCell($wDesc, 'DESCRIPTION');
        $this->headerCell($wSpecs, 'SPECS');
        $this->headerCell($wRawMat, 'RAW MATERIAL');
        $this->headerCell($wClassification, 'Classification');
        $this->headerCell($wMoq, 'MOQ');
        $this->headerCell($wUom, 'UOM');
        $this->headerCell($wPrice, 'Price/Pc', true);

        // Sub-headers row
        $rowH = 7;
        $this->fpdi->SetFont('Arial', 'B', 9);

        $this->fpdi->Cell($wPartCode, $rowH, '', 1, 0, 'C');
        $this->fpdi->Cell($wDesc, $rowH, '', 1, 0, 'C');
        $this->fpdi->Cell($wSpecsSub, $rowH, 'Length', 1, 0, 'C');
        $this->fpdi->Cell($wSpecsSub, $rowH, 'Width', 1, 0, 'C');
        $this->fpdi->Cell($wSpecsSub, $rowH, 'Height', 1, 0, 'C');

        $this->fpdi->Cell($wRawSub, $rowH, 'Type', 1, 0, 'C');
        $this->fpdi->Cell($wRawSub, $rowH, 'Thick', 1, 0, 'C');
        $this->fpdi->Cell($wRawSub, $rowH, 'Width', 1, 0, 'C');

        $this->fpdi->Cell($wClassification, $rowH, '', 1, 0, 'C');
        $this->fpdi->Cell($wMoq, $rowH, '', 1, 0, 'C');
        $this->fpdi->Cell($wUom, $rowH, '', 1, 0, 'C');
        $this->fpdi->Cell($wPrice, $rowH, '', 1, 1, 'C');

        // Reset font for body
        $this->fpdi->SetFont('Arial', '', 9);

        foreach ($products as $product) {
            // Normalize arrays
            $partCodeArr = is_array($product['part_code']) ? $product['part_code'] : [$product['part_code']];
            $descArr = is_array($product['description']) ? $product['description'] : [$product['description']];
            $lenArr  = is_array($product['length']) ? $product['length'] : [$product['length']];
            $widArr  = is_array($product['width']) ? $product['width'] : [$product['width']];
            $hgtArr  = is_array($product['height']) ? $product['height'] : [$product['height']];
            $matArr  = is_array($product['material']) ? $product['material'] : [$product['material']];
            $thkArr  = is_array($product['thickness']) ? $product['thickness'] : [$product['thickness']];
            $mwArr   = is_array($product['material_w']) ? $product['material_w'] : [$product['material_w']];
            $prices  = $product['prices'] ?? [];

            $subRows = max(count($descArr), count($lenArr), count($widArr), count($hgtArr), count($matArr), count($thkArr), count($mwArr));
            $priceRows = max(1, count($prices));

            $subRowHeight = 6;
            $minBlockHeight = $subRows * $subRowHeight;
            $priceRowHeight = 7;
            $minPriceBlockHeight = $priceRows * $priceRowHeight;
            $blockHeight = max($minBlockHeight, $minPriceBlockHeight)+10;
            $actualPriceRowH = $blockHeight / $priceRows;

            $x = $this->fpdi->GetX();
            $y = $this->fpdi->GetY();

            // Part Code box
            $this->fpdi->Rect($x, $y, $wPartCode, $blockHeight);
            $this->drawRectText($x, $y, $wPartCode, $blockHeight, $partCodeArr, $subRowHeight);

            // DESCRIPTION
            $dx = $x + $wPartCode;
            $this->fpdi->Rect($dx, $y, $wDesc, $blockHeight);
            $this->drawRectText($dx, $y, $wDesc, $blockHeight, $descArr, $subRowHeight);

            // SPECS
            $sx = $dx + $wDesc;
            $this->fpdi->Rect($sx, $y, $wSpecs, $blockHeight);
            $curY = $y;
            for ($r = 0; $r < $subRows; $r++) {
                $this->fpdi->SetXY($sx, $curY);
                $this->fpdi->Cell($wSpecsSub, $subRowHeight, $lenArr[$r] ?? '', 0, 0, 'C');
                $this->fpdi->Cell($wSpecsSub, $subRowHeight, $widArr[$r] ?? '', 0, 0, 'C');
                $this->fpdi->Cell($wSpecsSub, $subRowHeight, $hgtArr[$r] ?? '', 0, 0, 'C');
                $curY += $subRowHeight;
            }
            // draw vertical separators for SPECS subcols
            $this->fpdi->SetDrawColor(120);
            $this->fpdi->Line($sx + $wSpecsSub, $y, $sx + $wSpecsSub, $y + $blockHeight);
            $this->fpdi->Line($sx + 2*$wSpecsSub, $y, $sx + 2*$wSpecsSub, $y + $blockHeight);

            $rx = $sx + $wSpecs;
            $this->fpdi->Rect($rx, $y, $wRawMat, $blockHeight);
            $curY = $y;
            for ($r = 0; $r < $subRows; $r++) {
                $this->fpdi->SetXY($rx, $curY);
                $this->fpdi->Cell($wRawSub, $subRowHeight, $matArr[$r] ?? '', 0, 0, 'C');
                $this->fpdi->Cell($wRawSub, $subRowHeight, $thkArr[$r] ?? '', 0, 0, 'C');
                $this->fpdi->Cell($wRawSub, $subRowHeight, $mwArr[$r] ?? '', 0, 0, 'C');
                $curY += $subRowHeight;
            }
            // draw vertical separators for RAW MATERIAL subcols
            $this->fpdi->Line($rx + $wRawSub, $y, $rx + $wRawSub, $y + $blockHeight);
            $this->fpdi->Line($rx + 2*$wRawSub, $y, $rx + 2*$wRawSub, $y + $blockHeight);
            // RIGHT: Classification + Price
            $px = $rx + $wRawMat;
            $curPY = $y;
            for ($pr = 0; $pr < $priceRows; $pr++) {
                // Classification
                $this->fpdi->Rect($px, $curPY, $wClassification, $actualPriceRowH);
                $this->fpdi->SetXY($px, $curPY);
                $class = $prices[$pr][0] ?? '';
                $this->fpdi->Cell($wClassification, $actualPriceRowH, $class, 0, 0, 'C');

                // MOQ
                $this->fpdi->Rect($px + $wClassification, $curPY, $wMoq, $actualPriceRowH);
                $this->fpdi->SetXY($px + $wClassification, $curPY);
                $moq = $prices[$pr][1] ?? '';
                $this->fpdi->Cell($wMoq, $actualPriceRowH, $moq, 0, 0, 'C');

                // UOM
                $this->fpdi->Rect($px + $wClassification + $wMoq, $curPY, $wUom, $actualPriceRowH);
                $this->fpdi->SetXY($px + $wClassification + $wMoq, $curPY);
                $uom = $prices[$pr][2] ?? '';
                $this->fpdi->Cell($wUom, $actualPriceRowH, $uom, 0, 0, 'C');

                // Price
                $this->fpdi->Rect($px + $wClassification + $wMoq + $wUom, $curPY, $wPrice, $actualPriceRowH);
                $this->fpdi->SetXY($px + $wClassification + $wMoq + $wUom, $curPY);
                $priceText = $prices[$pr][3] ?? '';
                $this->fpdi->Cell($wPrice, $actualPriceRowH, $priceText, 0, 0, 'C');

                $curPY += $actualPriceRowH;
            }

            $this->fpdi->SetY($y + $blockHeight + 2);
        }
    }
    private function buildProductTable(array $products){

        // Column width strategy (proportional)
        $wPartCode = $this->usableWidth * 0.20; // Part Code
        $wDesc     = $this->usableWidth * 0.30; // DESCRIPTION
        $wClassification = $this->usableWidth * 0.20; // Classification
        $wMoq   = $this->usableWidth * 0.10;
        $wUom   = $this->usableWidth * 0.10;
        $wPrice = $this->usableWidth * 0.10;

        // Draw header
        $this->fpdi->SetFillColor(230, 230, 230);
        $this->fpdi->SetDrawColor(120);
        $this->fpdi->SetLineWidth(0.2);

        $this->headerCell($wPartCode, 'Part Code');
        $this->headerCell($wDesc, 'DESCRIPTION');
        $this->headerCell($wClassification, 'Classification');
        $this->headerCell($wMoq, 'MOQ');
        $this->headerCell($wUom, 'UOM');
        $this->headerCell($wPrice, 'Price/Pc', true);

        // Reset font for body
        $this->fpdi->SetFont('Arial', '', 9);

        foreach ($products as $product) {

            // Normalize arrays
            $partCodeArr = is_array($product['part_code']) ? $product['part_code'] : [$product['part_code']];

            $descArr = is_array($product['description']) ? $product['description'] : [$product['description']];

            $prices  = $product['prices'] ?? [];

            // return gettype($descArr);
            // return gettype($descArr);
            $subRows = max(count($descArr),0);

            $priceRows = max(1, count($prices));

            $subRowHeight = 6;
            $blockHeight = max($subRows * $subRowHeight, $priceRows * 7);
            $actualPriceRowH = $blockHeight / $priceRows;

            $x = $this->fpdi->GetX();
            $y = $this->fpdi->GetY();

            // Part Code box
            $this->fpdi->Rect($x, $y, $wPartCode, $blockHeight);
            $this->drawRectText($x, $y, $wPartCode, $blockHeight, $partCodeArr, $subRowHeight);

            // DESCRIPTION
            $dx = $x + $wPartCode;
            $this->fpdi->Rect($dx, $y, $wDesc, $blockHeight);
            $this->drawRectText($dx, $y, $wDesc, $blockHeight, $descArr, $subRowHeight);

            // RIGHT: Classification + Price
            $px = $dx + $wDesc;
            $curPY = $y;
            for ($pr = 0; $pr < $priceRows; $pr++) {
                // Classification
                $this->fpdi->Rect($px, $curPY, $wClassification, $actualPriceRowH);
                $this->fpdi->SetXY($px, $curPY);
                $class = $prices[$pr][0] ?? '';
                $this->fpdi->Cell($wClassification, $actualPriceRowH, $class, 0, 0, 'C');

                // MOQ
                $this->fpdi->Rect($px + $wClassification, $curPY, $wMoq, $actualPriceRowH);
                $this->fpdi->SetXY($px + $wClassification, $curPY);
                $moq = $prices[$pr][1] ?? '';
                $this->fpdi->Cell($wMoq, $actualPriceRowH, $moq, 0, 0, 'C');

                // UOM
                $this->fpdi->Rect($px + $wClassification + $wMoq, $curPY, $wUom, $actualPriceRowH);
                $this->fpdi->SetXY($px + $wClassification + $wMoq, $curPY);
                $uom = $prices[$pr][2] ?? '';
                $this->fpdi->Cell($wUom, $actualPriceRowH, $uom, 0, 0, 'C');

                // Price
                $this->fpdi->Rect($px + $wClassification + $wMoq + $wUom, $curPY, $wPrice, $actualPriceRowH);
                $this->fpdi->SetXY($px + $wClassification + $wMoq + $wUom, $curPY);
                $priceText = $prices[$pr][3] ?? '';
                $this->fpdi->Cell($wPrice, $actualPriceRowH, $priceText, 0, 0, 'C');

                $curPY += $actualPriceRowH;
            }

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
