<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class YearlyRevenueAnalyticService extends AnalyticsService
{
    public function yearlyRevenue()
    {
        $monthlyYields = $this->getMonthlyYields();

        $sortedData = $this->sortCustomerData($monthlyYields, 1);

        //calculating yearly revenue per customer
        $revenues = [];

        foreach($sortedData as $customerData)
        {
            $revenues[] = $this->calcYearlyCustomerRevenue($customerData,true);
        }

        //combine calculated revenue
        $montlyRevenue = $this->combineMonthlyRevenue($revenues);        
        $revenue = $this->combineYearlyRevenues($revenues);
        
        //making new spreadsheet
        $fileName = './public/Spreadsheets/yearlyRevenueOverview.xlsx';
        $spreadsheet = new Spreadsheet($fileName);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('yearly revenue Overview');

        $col = 'A';
        $row = 1;

        //setheaders
        $startDate = $this->findEurliestDate($sortedData);

        $dateList = $this->getDateList($startDate, 1);
        
        $this->setHeaders($sheet, $col, $row, ['Total revenue',$dateList]);

        //put data in sheet
        $col = 'A';
        $row = 2;
        
        // $sheet->setCellValue($col . $row, '€'.round($revenue,3));
        $col = $this->fillRow($sheet, $col, $row, $revenue,'€','',3);

        $col = $this->fillRow($sheet, $col, $row, $montlyRevenue,'€','',2);
        // foreach($montlyRevenue as $monthlyRevenue)
        // {
        //     $sheet->setCellValue($col . $row, '€'.round($monthlyRevenue,2));
        //     $col++;
        // }

        //make trendline

        $this->saveSheet($spreadsheet,$sheet, $fileName);

        return 'yearly revenue overview spreadsheet created. total revenue is: €'. round($revenue,2) .' . check ' . $fileName . ' for more results';
    }
}