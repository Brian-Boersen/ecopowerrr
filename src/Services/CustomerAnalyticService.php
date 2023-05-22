<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


class CustomerAnalyticService extends AnalyticsService
{
    public function customerOverview($timeframe = "m")
    {
        $timeframes =
        [
            'y' => 12,
            'q' => 3,
            'm' => 1
        ];

        $timeframe = $timeframes[$timeframe];

        $monthlyYields = $this->getMonthlyYields();

        $sortedData = $this->sortCustomerData($monthlyYields, $timeframe);

        //making new spreadsheet
        $fileName = './public/Spreadsheets/customerOverview.xlsx';
        $spreadsheet = new Spreadsheet($fileName);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Customer Overview');

        $col = 'A';
        $row = 1;

        $this->setCustomerHeaders($sheet, $col, $row, $timeframe,reset($sortedData)[0]->getStartDate());

        $col = 'A';
        $row = 2;

        $this->setCustomerData($sheet, $col, $row, $sortedData);
        
        $this->saveSheet($spreadsheet,$sheet, $fileName);

        return 'Customer Overview spreadsheet created. check' . $fileName . ' for the results';
    }
}