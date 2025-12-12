<?php

namespace App\Services;

use App\Interfaces\PdfInterface;
use setasign\Fpdi\Fpdi;

class PdfService implements PdfInterface
{
    private $fpdi;




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
    public function generatePdfProductMaterialSingleRow($data)
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
    private function buildProductTableSingleRow($products) {
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
            $this->fpdi->MultiCell($wDesc, 8, $product["description"], 1);
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
    private function headerCellSingleRow($width, $label, $endRow = false)
    {
        $this->fpdi->SetFillColor(230, 230, 230);
        $this->fpdi->Cell($width, 8, $label, 1, $endRow ? 1 : 0, 'C', true);
        $this->fpdi->SetFillColor(255, 255, 255);
    }
}
