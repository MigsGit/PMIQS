<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\ChangeControlManagementSheet;

class ExternalCcmExport implements
WithMultipleSheets
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $ecrsCategoryDetailsCollection;

    public function __construct($ecrsCategoryDetailsCollection)
    {
        $this->ecrsCategoryDetailsCollection = $ecrsCategoryDetailsCollection;
    }

    public function sheets(): array{
        $sheets = [];
        $sheets['External Report'] = new ChangeControlManagementSheet($this->ecrsCategoryDetailsCollection);
        return $sheets;
    }
    public function collection()
    {
        return $this->ecrsCategoryDetailsCollection;
    }
}
