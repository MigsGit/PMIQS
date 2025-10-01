<?php

namespace App\Exports;

use App\Exports\Sheets\InternalMachineSheet;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InternalMachineExport implements WithMultipleSheets
{
   protected $test;

    public function __construct($test)
    {
        $this->test = $test;
    }

    public function sheets(): array{
        $sheets = [];
        $sheets['Machine'] = new InternalMachineSheet($this->test);
        return $sheets;
    }
    public function collection()
    {
        // Wrap string in a Collection
        return new Collection([
            ['Value'], // column headers
            [$this->test], // data
        ]);
    }
}
