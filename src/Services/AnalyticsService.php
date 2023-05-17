<?php

namespace App\Services;

use App\Repository\ContractRepository;
use App\Repository\CustomerRepository;
use App\Repository\DevicesRepository;
use App\Repository\MothlyYieldRepository;
use DateTime;
use PhpOffice\PhpSpreadsheet\IOFactory;
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
        $this->setHeaders($sheet, $col, $row, [1,2,3,4,5,6,7,8,9,10,11,12]);

        //put data in sheet
        $col = 'A';
        $row = 2;
        foreach($montlyRevenue as $revenue)
        {
            $sheet->setCellValue($col . $row, $revenue);
            $col++;
        }
        //make trendline

        foreach ($sheet->getColumnIterator() as $column)
        {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }
                
        //save sheet
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save($fileName);

        return 'yearly revenue overview spreadsheet created. total revenue is: €'. round($revenue,2) .' . check ' . $fileName . ' for more results';
    }

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
        
        foreach ($sheet->getColumnIterator() as $column)
        {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }
                
        //save sheet
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save($fileName);

        return 'Customer Overview spreadsheet created. check' . $fileName . ' for the results';
    }

    private function setHeaders($sheet, $col, $row, $headers)
    {
        for($i = 0; $i < count($headers); $i++)
        {
            $sheet->setCellValue($col.$row, $headers[$i]);
            $sheet->getStyle($col.$row)->getFont()->setBold(true);
            $col++;
        }

        return $col;
    } 

    private function setCustomerHeaders($sheet, $col, $row, $timeframe, $startdate)
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

        $col = $this->setHeaders($sheet, $col, $row, $headers);

        for($i = 0; $i < (12/$timeframe); $i++)
        {
            $sheet->setCellValue($col.$row, $newStartDate->format('m-Y') . ' - ' . $newEndDate->format('m-Y'));
            $sheet->getStyle($col.$row)->getFont()->setBold(true);

            date_modify($newStartDate, '+' . (1 * $timeframe) . ' month');
            date_modify($newEndDate, '+' . (1 * $timeframe) . ' month');
            
            $col++;
        }
    }

    private function sortCustomerData($yieldData, $timeframe)
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

        foreach($data as $checkValue)
        {
            $newValue = true;

            $checkStartDate = $checkValue->getStartDate()->format('Ymd');

            for($i = count($mergedData) - 1; $i >= 0; $i--)
            {
                $compareValue = $mergedData[$i];

                $compareStartDate = $compareValue->getStartDate()->format('Ymd');

                $compareEndDate = new DateTime($compareValue->getStartDate()->format('y-m-d'));
                $compareEndDate = date_modify($compareEndDate, '+' . (1 * $timeframe) . ' month');
                $compareEndDate = $compareEndDate->format('Ymd');

                if((int)$checkStartDate >= (int)$compareStartDate && (int)$checkStartDate < (int)$compareEndDate)
                {
                    $compareValue->setYield($compareValue->getYield() + $checkValue->getYield());
                    $compareValue->setSurplus($compareValue->getSurplus() + $checkValue->getSurplus());
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

    private function combineMonthlyRevenue($revenue)
    {
        $monthlyArray = [];
        $monthlyRevenue = [];

        $newItem = true;

        foreach($revenue as $customerRev)
        {
            foreach($customerRev as $data)
            {
                $newItem = true;

                $yieldStartDate = $data[0]->getStartDate()->format('ymd');

                foreach($monthlyArray as $key => $monthlyVal)
                {
                    $monthlyStartDate = $monthlyVal[0]->getStartDate()->format('ymd');

                    if((int)$yieldStartDate == (int)$monthlyStartDate)      
                    {
                        $combinedEntry = $monthlyVal[1] + $data[1];
                        $monthlyArray[$key][1] = $combinedEntry;
                        $newItem = false;
                        break;
                    } 
                }

                if($newItem == true)
                {
                    $monthlyArray[] = $data;
                }   
            }
        }

        foreach($monthlyArray as $monthlyVal)
        {
            $monthlyRevenue[] =  $monthlyVal[1];
        }

        return $monthlyRevenue;
    }

    private function combineYearlyRevenues($Revenues)
    {
        $yearlyRevenue = 0;

        foreach($Revenues as $value)
        {
            foreach($value as $key => $val)
            {
                $yearlyRevenue += $val[1];
            }
        }

        return $yearlyRevenue;
    }

    private function calcYearlyCustomerRevenue($customerData,$separate = false)
    {
        // $contracts = $this->getContractsBy('customer',  $customerData[0]->getDevice()->getCustomer()->getId()); 
        $contracts = $this->contractRepository->findAll();
        
        $yearlyRevenue = 0;

        if($separate === true)
        {
            $yearlyRevenue = [];
        }

        foreach($customerData as $data)
        {
            if(!$contracts)
            {
                return null;
            }

            $dataCustomerId = $data->getDevice()->getCustomer()->getId();

            $sellPrice = $contracts[0]->getSellPrice() / 100;
            $buyPrice = $contracts[0]->getBuyPrice() / 100;

            foreach($contracts as $contract)
            {
                if($contract->getCustomer()->getId() != $dataCustomerId)
                {
                    continue;
                }

                $dataStartDate = $data->getStartDate()->format('m-Y');
                $contractStartDate = $contract->getStartDate()->format('m-Y');
                $contractEndDate = $contract->getEndDate()->format('m-Y');

                if($dataStartDate >= $contractStartDate && $dataStartDate <= $contractEndDate)
                {
                    //I would calc revenue here if the data was real
                    $sellPrice = $contract->getSellPrice() / 100;
                    $buyPrice = $contract->getBuyPrice() / 100;
                }
            }
            
            $revenue = ($data->getYield() - $data->getSurplus()) * $sellPrice - ($data->getSurplus() * $buyPrice);

            if($separate === true)
            {
                $yearlyRevenue[] = [$data,$revenue];
            }
            else
            {
                $yearlyRevenue += $revenue;
            }
        }
                
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
            $yearlyRevenue = round($this->calcYearlyCustomerRevenue($customerData), 2);

            $sheet->setCellValue($col.$row,'€ ' . $yearlyRevenue);
            //
            $col = 'E';

            foreach($customerData as $data)
            {
                $dataStartDate = $data->getStartDate()->format('Ymd');

                $loop = true;

                while($loop)
                {
                    $headerVal = $sheet->getCell($col.'1')->getValue();

                    if($headerVal == null)
                    {
                        print_r('Error: No header found for data on: ' . $col . '1 -');
                        break;
                    }

                    $headerVals = explode(' - ', $headerVal);
                    $startDateParts = explode('-', $headerVals[0]);
                    $endDateParts = explode('-', $headerVals[1]);
                    
                    $headerDates = 
                    [
                        $startDateParts[1].''.$startDateParts[0].'01',
                        $endDateParts[1].''.$endDateParts[0].'01'
                    ];

                    if((int)$dataStartDate >= (int)$headerDates[0] && (int)$dataStartDate < (int)$headerDates[1])
                    {
                        break;
                        $loop = false;
                    }

                    $col++;
                }

                $sheet->setCellValue($col.$row,$data->getSurplus() . ' Kwh');
                // $sheet->setCellValue($col.$row,$data->getStartDate()->format('m-Y'));

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

    private function getContractsBy($field, $value)
    {
        return $this->contractRepository->findBy([$field => $value]);
    }

    private function getMonthlyYields()
    {
        return $this->monthlyYieldRepository->findAll();
    }
}

