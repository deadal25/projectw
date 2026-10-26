<?php

namespace App\Exports;

use App\Exports\Sheets\WolaSummarySheet;
use App\Exports\Sheets\WolaDetailsSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WolaReportExport implements WithMultipleSheets
{
    protected $groupId;
    protected $jobId;
    protected $assessorId;
    protected $employeeId;

    public function __construct($groupId = null, $jobId = null, $assessorId = null, $employeeId = null)
    {
        $this->groupId = $groupId;
        $this->jobId = $jobId;
        $this->assessorId = $assessorId;
        $this->employeeId = $employeeId;
    }

    public function sheets(): array
    {
        return [
            new WolaSummarySheet($this->groupId),
            new WolaDetailsSheet($this->groupId, $this->jobId, $this->assessorId, $this->employeeId),
        ];
    }
}
