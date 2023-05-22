<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class MunicipalityAnalyticService extends AnalyticsService
{
    public function municipalityOverview()
    {
        //get customers
        $monthlyYields = $this->getMonthlyYields();

        //sort customers
        $customers = $this->getCustomers();
        $yields = $this->sortCustomerData($monthlyYields, 12);

        //split customers by municipality
        $municipalities = $this->splitOnMunicipality($customers, $yields);

            //make spreadsheet
        $fileName = './public/Spreadsheets/municipalityOverview.xlsx';
        $spreadsheet = new Spreadsheet($fileName);

        //get active sheet
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Municipality Overview');

        $col = 'A';
        $row = 1;

        //set headers
        $this->setHeaders($sheet, $col, $row, ['Municipality','Total revenue','Total yield (KWH)','Total surplus (KWH)']);

        //fill cells
        $col = 'A';
        $row = 2;

        foreach($municipalities as $municipality => $yields)
        {
            $revenue = $this->calcYearlyCustomerRevenue($yields);
            
            $totalYields = $this->calcYearlyCustomerYield($yields);
            $totalSurplus = $this->calcYearlyCustomerSurplus($yields);

            $this->fillRow($sheet, $col, $row, [$municipality,'€'.round($revenue,2),$totalYields,$totalSurplus],'','',2);

            $row++;
        }

        //space cells and save sheet
        $this->saveSheet($spreadsheet,$sheet, $fileName);

        return 'Municipality Overview spreadsheet created. check: ' . $fileName . ' for the results';

    }
}