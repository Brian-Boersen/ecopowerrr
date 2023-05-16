<?php

namespace App\Services;

use App\Entity\Customer;
use App\Repository\ContractRepository;
use App\Repository\CustomerRepository;
use App\Repository\DevicesRepository;
use App\Repository\MothlyYieldRepository;
use DateTime;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class AnalyticsService
{
    //constructor
    public function __construct
    (
        private CustomerRepository $customerRepository,
        private ContractRepository $contractRepository,
        private DevicesRepository $deviceRepository,
        private MothlyYieldRepository $monthlyYieldRepository,
    ){}

    public function CustomerOverview($timeframe = "m")
    {
        $timeframes =
        [
            'y' => 12,
            'q' => 3,
            'm' => 1
        ];

        $timeframe = $timeframes[$timeframe];

        $monthlyYields = $this->getMonthlyYields();

        $sortedData = $this->SortCustomerData($monthlyYields, $timeframe);

        //making new spreadsheet
        $fileName = './public/Spreadsheets/customerOverview.xlsx';
        $spreadsheet = new Spreadsheet($fileName);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Customer Overview');

        $col = 'A';
        $row = 1;

        $this->setHeaders($sheet, $col, $row, $timeframe,reset($sortedData)[0]->getStartDate());

        $col = 'A';
        $row = 2;

        $this->setCustomerData($sheet, $col, $row, $sortedData);
        
        foreach ($sheet->getColumnIterator() as $column)
        {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }
                
        //save sheet
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save($fileName);

        return 'Customer Overview spreadsheet created. check' . $fileName . ' for the results';
    }

    private function setHeaders($sheet, $col, $row, $timeframe, $startdate)
    {
        $newStartDate = new DateTime($startdate->format('Y-m-d'));
        $newEndDate = new DateTime($startdate->format('Y-m-d'));
        
        date_modify($newEndDate, '+' . (1 * $timeframe) . ' month');

        $headers = [
            'Customer ID',
            'Customers',
            'Yearly revenue',
            'Bought Kwh -->',
        ];     

        for($i = 0; $i < count($headers); $i++)
        {
            $sheet->setCellValue($col.$row, $headers[$i]);
            $sheet->getStyle($col.$row)->getFont()->setBold(true);
            $col++;
        }

        for($i = 0; $i < (12/$timeframe); $i++)
        {
            $sheet->setCellValue($col.$row, $newStartDate->format('m-Y') . ' - ' . $newEndDate->format('m-Y'));
            $sheet->getStyle($col.$row)->getFont()->setBold(true);

            date_modify($newStartDate, '+' . (1 * $timeframe) . ' month');
            date_modify($newEndDate, '+' . (1 * $timeframe) . ' month');
            
            $col++;
        }
    }

    private function SortCustomerData($yieldData, $timeframe)
    {
        $customers = $this->getCustomers();

        $sortedData = [];

        foreach($customers as $customer)
        {
            $customerData = [];

            foreach($yieldData as $key => $value)
            {
                if($value->getDevice()->getCustomer()->getID() == $customer->getID())
                {
                    $customerData[] = $value;     
                    unset($yieldData[$key]);
                }
            }

            $customerData = $this->mergeOnDate($customerData, $timeframe);

            usort($customerData, function($a, $b) 
            {
                return $a->getStartDate() <=> $b->getStartDate();
            });
            
            $sortedData[$customer->getId()] = $customerData;
        }

        return $sortedData;
    }

    private function mergeOnDate($data, $timeframe)
    {
        $mergedData = [];

        usort($data, function($a, $b) 
        {
            return $a->getStartDate() <=> $b->getStartDate();
        });

        $testcount = 0;
        $testcount2 = 0;

        foreach($data as $checkValue)
        {
            $testcount2 = 0;
            $testcount++;

            $newValue = true;

            $checkStartDate = $checkValue->getStartDate()->format('Y-m-d');

            for($i = count($mergedData) - 1; $i >= 0; $i--)
            {
                $compairValue = $mergedData[$i];
                $testcount2++;
                $compairStartDate = $compairValue->getStartDate()->format('Y-m-d');

                $compairEndDate = new DateTime($compairStartDate);
                $compairEndDate = date_modify($compairEndDate, '+' . (1 * $timeframe) . ' month');
                $compairEndDate = $compairEndDate->format('Y-m-d');

                if($checkStartDate >= $compairStartDate && $checkStartDate < $compairEndDate)
                {
                    $compairValue->setYield($compairValue->getYield() + $checkValue->getYield());
                    $compairValue->setSurplus($compairValue->getSurplus() + $checkValue->getSurplus());
                    $newValue = false;
                    break;
                }
            }

            if($newValue == true)
            {
                $mergedData[] = $checkValue;
            }
        }

        return $mergedData;
    }

    private function calcYearlyRevenue($customerData)
    {
        $contracts = $this->contractRepository->findBy(['customer' => $customerData[0]->getDevice()->getCustomer()->getId()]);
        $yearlyRevenue = 0;

        foreach($customerData as $data)
        {
            if(!$contracts)
            {
                return null;
            }

            $sellPrice = $contracts[0]->getSellPrice() / 100;
            $buyPrice = $contracts[0]->getBuyPrice() / 100;

            foreach($contracts as $contract)
            {
                $dataStartDate = $data->getStartDate()->format('m-Y');
                $contractStartDate = $contract->getStartDate()->format('m-Y');
                $contractEndDate = $contract->getEndDate()->format('m-Y');

                if($dataStartDate >= $contractStartDate && $dataStartDate <= $contractEndDate)
                {
                    //would calc revenue here if the data was real
                    $sellPrice = $contract->getSellPrice() / 100;
                    $buyPrice = $contract->getBuyPrice() / 100;
                }
            }

            $yearlyRevenue += ($data->getYield() - $data->getSurplus()) * $sellPrice - ($data->getSurplus() * $buyPrice);
        }
        
        $yearlyRevenue = round($yearlyRevenue, 2);
        
        return $yearlyRevenue; 
    }

    private function setCustomerData($sheet, $col, $row, $sortedData)
    {
        foreach($sortedData as $customerData)
        {
            $currentCustomer = $customerData[0]->getDevice()->getCustomer();

            $sheet->setCellValue($col.$row, $currentCustomer->getId());

            $col++;

            $sheet->setCellValue($col.$row, $currentCustomer->getFirstName().' '.$customerData[0]->getDevice()->getCustomer()->getLastName());
            
            $col++;
            //calc yearly revenue
            $sheet->setCellValue($col.$row,'€ ' . $this->calcYearlyRevenue($customerData));
            //
            $col = 'E';

            foreach($customerData as $data)
            {
                $sheet->setCellValue($col.$row,$data->getSurplus() . ' Kwh');
                $col++;
            }
            $row++;
            $col = 'A';
        }
    }

    private function getCustomers()
    {
        return $this->customerRepository->findAll();
    }

    private function getContracts()
    {
        return $this->contractRepository->findAll();
    }

    private function getDevices()
    {
        return $this->deviceRepository->findAll();
    }

    private function getMonthlyYields()
    {
        return $this->monthlyYieldRepository->findAll();
    }
}

