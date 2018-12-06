<?php

namespace Etg\ETGCustom\ExternalDataHub\FileProcess;

use Illuminate\Support\Facades\Storage;
use Etg\Cms\Models\Price;
use Etg\Cms\Models\Sailing;

/**
 * Class ProcessPricingData
 * @package Etg\ETGCustom\ExternalDataHub\FileProcess
 */
class ProcessPricingData
{


    /**
     * Description: This inserts pricing records 1 thousand records at a time
     *
     * @param $vendorId
     * @param $dataSource
     * @param $currencyCode
     */
    public function insertPricing($vendorId, $dataSource, $currencyCode)
    {

        // get data
        $exists = Storage::disk('local')->exists($dataSource);
        if(!$exists)
        {
            dd('unable to find json file ' . $dataSource);
        }

        $string = Storage::get($dataSource);
        $json_a = json_decode($string, true);

        $sailings = Sailing::where('vendor_id', $vendorId)->pluck('sailing_id')->toArray();

        Price::whereIn('sailing_id',$sailings)->
               where('currency_code',$currencyCode)->delete();

        $bulkArray = [];
        $totalRows = count($json_a['Dataset']);
        $counter = 0;

        // if no data, leave
        if(count($json_a['Dataset']) == 0)
        {
            return;
        }

        foreach ($json_a['Dataset'] as $item) {

            $prePaidGratuities = ($item['PrepaidGratuity'] = null ? $item['PrepaidGratuity'] : 0);

            array_push($bulkArray, array('sailing_id' => $item['SailingID'], 'sailing_price_id' => $item['SailingPriceID'], 'market_name' => $item['MarketName'],
                'currency_code' => $item['CurrencyCode'], 'rate_code' => $item['RateCode'], 'category_code' => $item['CategoryCode'],
                'adult_fare' => $item['AdultFare'], 'nccf' => $item['NCCF'], 'is_nccf_included' => $item['IsNCCFIncluded'],
                'tax' => $item['Tax'], 'is_tax_included' => $item['IsTaxIncluded'], 'prepaid_gratuity' => $prePaidGratuities));

            // chunk into 1k groups
            if ($counter == 1000 || $counter == $totalRows) {

                // Insert group
                Price::insert($bulkArray);

                // update counters
                $totalRows = $totalRows - 1000;
                $counter = 0;
                $bulkArray = [];

            }
            else {
                $counter++;
            }
        }
        // commit straglers
        Price::insert($bulkArray);
    }
}