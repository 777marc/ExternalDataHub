<?php

namespace Etg\ETGCustom\ExternalDataHub\PostProcess;

use Etg\ETGCustom\AlgoliaSync;
use Illuminate\Support\Facades\DB;

/**
 * Class AlgoliaRiverCruiseIndex
 * @package Etg\ETGCustom\ExternalDataHub\PostProcess
 */
class AlgoliaRiverCruiseIndex
{
    /**
     * @var string
     */
    private $query = "insert into river_cruise_searches (SailingPlanID, 
                                                         Title, 
                                                         VendorName, 
                                                         VendorImage, 
                                                         ShipName, 
                                                         ship_id, 
                                                         Date, 
                                                         Date_, 
                                                         sort_date, 
                                                         Duration, 
                                                         region, 
                                                         map_img, 
                                                         min_adult_fare, 
                                                         min_adult_fare_ca,                                                         
                                                         preferred, 
                                                         cities, 
                                                         rivers,
                                                         ResultType)
                        select river_sailings.sailing_id AS 'SailingPlanID',
                               river_sailings.sailing_name AS 'Title',
                               river_vendors.name AS 'VendorName',
                               river_vendors.image_path as 'VendorImage',
                               river_sailings.ship_name AS 'ShipName',
                               river_sailings.ship_id,
                               DATE_FORMAT(river_sailings.departure_date,'%m/%d/%Y') AS 'Date',
                               river_sailings.departure_date AS 'Date_',
                               UNIX_TIMESTAMP(river_sailings.departure_date) as 'sort_date',
                               river_sailings.duration AS 'Duration',
                               river_destinations.name AS 'region',
                               river_destinations.map_image_path as 'map_img',
                               pmus.min_adult_fare as 'min_adult_fare',
                               pmca.min_adult_fare as 'min_adult_fare_ca',
                               CASE
                                  WHEN river_vendors.preferred = 1 THEN 'Preferred'
                                  ELSE 'Not Preferred'
                               END AS 'preferred',
                               river_cities.cities,
                               river_waterways.rivers,
                               'rivercruise' as 'ResultType'
                        from river_sailings
                        LEFT JOIN river_vendors
                        on river_sailings.vendor_id = river_vendors.vendor_id
                        LEFT JOIN river_destinations
                        ON river_sailings.destination_id = river_destinations.destination_id
                        LEFT JOIN river_cities
                        ON river_sailings.sailing_id = river_cities.sailing_id
                        LEFT JOIN pricing_min pmus
                        ON (river_sailings.sailing_id = pmus.sailing_id AND pmus.currency = 'USD')
                        LEFT JOIN pricing_min pmca
                        ON (river_sailings.sailing_id = pmca.sailing_id AND pmca.currency = 'CAD')
                        LEFT JOIN river_waterways
                        ON river_sailings.sailing_id = river_waterways.sailing_id
                        WHERE river_sailings.departure_date > '2017-10-03'
                        ORDER BY river_sailings.departure_date DESC;";

    /**
     * Description: generates the river cruise algolia index
     *
     */
    public function syncAlgoliaCruiseIndex()
    {
        // Truncate CruiseSearch
        DB::table('river_cruise_searches')->truncate();

        // Populate CruiseSearch
        DB::select(DB::raw($this->query));

        // Update the index
        $algoliaSync = new AlgoliaSync();
        $algoliaSync->replaceIndex('RiverCruiseSearch', 'river_cruises_all_v1');
    }

}