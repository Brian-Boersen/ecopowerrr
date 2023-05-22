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
        protected CustomerRepository $customerRepository,
        protected ContractRepository $contractRepository,
        protected DevicesRepository $deviceRepository,
        protected MothlyYieldRepository $monthlyYieldRepository,
    ){}

    protected function fillRow($sheet, $col, $row, $values,$prefix = "",$suffix = "",$round = 0)
    {
        $loops = is_array($values)? count($values): 1;
        
        for($i = 0; $i < $loops; $i++)
        {
            $inVal = (is_array($values)) ?  $values[$i]: $values;

            if($round > 0 && is_float($inVal))
            {
                $inVal = round($inVal,$round);
            }

            $sheet->setCellValue($col.$row, $prefix . $inVal . $suffix);
            $col++;
        }

        return $col;
    }

    protected function findEurliestDate($data)
    {
        $dates = $this->colapseArray($data);
        $eurliestDate = null;

        foreach($dates as $value)
        {
            if(!($value->getStartDate() instanceof DateTime))
            {
                continue;
            }
            
            if($eurliestDate == null)
            {
                $eurliestDate = $value->getStartDate();
            }

            if((int)($eurliestDate->format("ymd")) > (int)($value->getStartDate()->format("ymd")))
            {
                $eurliestDate = $value->getStartDate()->format("m-Y");
            }
        }

        return $eurliestDate;
    }
    

    protected function colapseArray($array)
    {
        $newArray = [];

        array_walk_recursive($array, function($a) use (&$newArray) { $newArray[] = $a; });

        return $newArray;
    }

    protected function setHeaders($sheet, $col, $row, $headers)
    {
        $allHeaders = $this->colapseArray($headers);

        foreach($allHeaders as $header)
        {
            $sheet->setCellValue($col.$row, $header);
            $sheet->getStyle($col.$row)->getFont()->setBold(true);
            $col++;
        }

        return $col;
    } 

    protected function splitOnMunicipality($customers,$customerYields)
    {
        $municipalities = [];

        foreach($customerYields as $key => $yields)
        {
            foreach($customers as $customer)
            {
                if($key == $customer->getId())
                {
                    $municipality = $customer->getMunicipality();

                    if(!isset($municipalities[$municipality]))
                    {
                        $municipalities[$municipality] = [];
                    }

                    $municipalities[$municipality][] = $yields[0];
                }
            }
        }

        return $municipalities;
    }

    protected function setCustomerHeaders($sheet, $col, $row, $timeframe, $startdate)
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

    protected function getDateList(DateTime $startdate, $timeframe)
    {
        $newStartDate = new DateTime($startdate->format('Y-m-d'));
        $newEndDate = new DateTime($startdate->format('Y-m-d'));

        date_modify($newEndDate, '+' . (1 * $timeframe) . ' month');            


        $sortedDates = [];

        for($i = 0; $i < (12/$timeframe); $i++)
        {
            $sortedDates[] = $newStartDate->format('m-Y') . ' - ' . $newEndDate->format('m-Y');

            date_modify($newStartDate, '+' . (1 * $timeframe) . ' month');
            date_modify($newEndDate, '+' . (1 * $timeframe) . ' month');            
        }
        

        return $sortedDates;
    }

    protected function sortCustomerData($yieldData, $timeframe)
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
            
            $sortedData[$customer->getId()] = $customerData;
        }

        return $sortedData;
    }

    protected function mergeOnDate($data, $timeframe)
    {
        $mergedData = [];

        foreach($data as $checkYield)
        {
            $newValue = true;

            $checkStartDate = $checkYield->getStartDate()->format('Ymd');

            for($i = count($mergedData) - 1; $i >= 0; $i--)
            {
                $compareYield = $mergedData[$i];

                $compareStartDate = $compareYield->getStartDate()->format('Ymd');

                $compareEndDate = new DateTime($compareYield->getStartDate()->format('y-m-d'));
                $compareEndDate = date_modify($compareEndDate, '+' . (1 * $timeframe) . ' month');
                $compareEndDate = $compareEndDate->format('Ymd');

                if((int)$checkStartDate >= (int)$compareStartDate && (int)$checkStartDate < (int)$compareEndDate)
                {
                    $compareYield->setYield($compareYield->getYield() + $checkYield->getYield());
                    $compareYield->setSurplus($compareYield->getSurplus() + $checkYield->getSurplus());
                    $newValue = false;
                    break;
                }
            }

            if($newValue == true)
            {
                $mergedData[] = $checkYield;
            }
        }

        usort($mergedData, function($a, $b) 
        {
            return $a->getStartDate() <=> $b->getStartDate();
        });

        return $mergedData;
    }

    protected function calcYearlyCustomerYield($data)
    {
        $yearlyYield = 0;

        foreach($data as $data)
        {
            $yearlyYield += $data->getYield();
        }

        return $yearlyYield;
    }

    protected function calcYearlyCustomerSurplus($data)
    {
        $yearlySurplus = 0;

        foreach($data as $data)
        {
            $yearlySurplus += $data->getSurplus();
        }

        return $yearlySurplus;
    }

    protected function combineMonthlyRevenue($revenue)
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

    protected function combineYearlyRevenues($Revenues)
    {
        $yearlyRevenue = 0;

        foreach($Revenues as $value)
        {
            foreach($value as $val)
            {
                $yearlyRevenue += $val[1];
            }
        }

        return $yearlyRevenue;
    }

    protected function calcYearlyCustomerRevenue($customerData,$separate = false)
    {
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

            $customerId = $data->getDevice()->getCustomer()->getId();

            $sellPrice = $contracts[0]->getSellPrice() / 100;
            $buyPrice = $contracts[0]->getBuyPrice() / 100;

            foreach($contracts as $contract)
            {
                if($contract->getCustomer()->getId() != $customerId)
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

    protected function setCustomerData($sheet, $col, $row, $sortedData)
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

                $col++;
            }

            $row++;
            $col = 'A';
        }
    }

    protected function saveSheet($spreadsheet, $sheet, $fileName)
    {
        //space cells
        foreach ($sheet->getColumnIterator() as $column)
        {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }
                
        //save sheet
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save($fileName);
    }

    protected function getCustomers()
    {
        return $this->customerRepository->findAll();
    }

    protected function getContractsBy($field, $value)
    {
        return $this->contractRepository->findBy([$field => $value]);
    }

    protected function getMonthlyYields()
    {
        return $this->monthlyYieldRepository->findAll();
    }
}

