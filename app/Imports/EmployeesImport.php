<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeesImport implements ToArray, WithHeadingRow
{
    /**
     * Parsing baris spreadsheet menjadi array
     */
    public function array(array $rows)
    {
        return $rows;
    }
}
