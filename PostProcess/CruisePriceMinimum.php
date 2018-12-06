<?php

namespace Etg\ETGCustom\ExternalDataHub\PostProcess;

use Illuminate\Support\Facades\DB;

/**
 * Class CruisePriceMinimum
 * @package Etg\ETGCustom\ExternalDataHub\PostProcess
 */
class CruisePriceMinimum
{
    /**
     * @var string
     */
    private $query_usd = "INSERT INTO pricing_min (sailing_id, min_adult_fare,currency)
                          SELECT sailing_id, MIN(adult_fare), currency_code
                          FROM prices
                          WHERE currency_code = 'USD'
                          GROUP BY sailing_id;";

    /**
     * @var string
     */
    private $query_cad = "INSERT INTO pricing_min (sailing_id, min_adult_fare,currency)
                          SELECT sailing_id, MIN(adult_fare), currency_code
                          FROM prices
                          WHERE currency_code = 'CAD'
                          GROUP BY sailing_id;";

    /**
     * Description: seeds price_min table
     */
    public function setMinimumPrices()
    {
        // Truncate pricing_min
        DB::table('pricing_min')->truncate();

        // Populate pricing_min w/ usd
        DB::select(DB::raw($this->query_usd ));

        // Populate pricing_min w/ cad
        DB::select(DB::raw($this->query_cad));
    }
}