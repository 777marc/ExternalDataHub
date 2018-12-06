<?php

namespace Etg\ETGCustom\ExternalDataHub\PostProcess;

use Etg\ETGCustom\AlgoliaSync;
use Illuminate\Support\Facades\DB;

/**
 * Class AlgoliaCruiseIndex
 * @package Etg\ETGCustom\ExternalDataHub\PostProcess
 */
class AlgoliaOceanCruiseIndex
{

    private $query = "insert into cruise_searches (SailingPlanID, 
                                                   Title, 
                                                   VendorName, 
                                                   VendorImage, 
                                                   ShipName, 
                                                   Date, 
                                                   Date_, 
                                                   sort_date, 
                                                   Duration, 
                                                   port, 
                                                   region, 
                                                   map_img, 
                                                   min_adult_fare, 
                                                   min_adult_fare_ca, 
                                                   InterimPorts, 
                                                   promotion_id, 
                                                   legacy_offer_id, 
                                                   preferred, 
                                                   callout,
                                                   ResultType, 
                                                   exclude_AU)
                               select sailings.sailing_id AS 'SailingPlanID',
                               sailings.sailing_name AS 'Title',
                               vendors.name AS 'VendorName',
                               vendors.image_path as 'VendorImage',
                               sailings.ship_name AS 'ShipName',
                               DATE_FORMAT(sailings.departure_date,'%m/%d/%Y') AS 'Date',
                               sailings.departure_date AS 'Date_',
                               UNIX_TIMESTAMP(sailings.departure_date) as 'sort_date',
                               sailings.duration AS 'Duration',
                               ports.name AS 'port',
                               destinations.name AS 'region',
                               destinations.map_image_path as 'map_img',
                               pmus.min_adult_fare as 'min_adult_fare',
                               pmca.min_adult_fare as 'min_adult_fare_ca',
                               interim_ports.port_list as 'InterimPorts',
                               CASE
                                  WHEN INSTR(sailings.promotion_text,'Hosted') > 0 THEN 'Hosted Cruise Exclusives'
                                  WHEN LENGTH(sailings.promotion_text) > 0 THEN 'Ensemble Exclusive'
                                  ELSE 'No Offer'
                               END AS 'promotion_id',
                               null as 'legacy_offer_id',
                               CASE
                                  WHEN vendors.preferred = 1 THEN 'Preferred'
                                  ELSE 'Not Preferred'
                               END AS 'preferred',
                               sailings.promo_callout as 'callout',
                               'cruise' as 'ResultType',
                              CASE WHEN vendors.name IN ('AmaWaterways','Azamara Club Cruises','Celebrity Cruises','Crystal Cruises',
                                      'Cunard','Holland America Line','Norwegian Cruise Line','Oceania Cruises',
                                      'Paul Gauguin Cruises','Ponant','Princess Cruises','Regent Seven Seas Cruises','Royal Caribbean International',
                                      'Seabourn Cruise Line','Silversea Cruises','Viking Ocean Cruises','Windstar Cruises')
                                THEN 1 ELSE 0
                              END AS exclude_AU
                        from sailings
                        inner JOIN vendors
                        on sailings.vendor_id = vendors.vendor_id
                        LEFT JOIN ports
                        on sailings.departure_port_id = ports.port_id
                        LEFT JOIN destinations
                        ON sailings.destination_id = destinations.destination_id
                        LEFT JOIN pricing_min pmus
                        ON (sailings.sailing_id = pmus.sailing_id AND pmus.currency = 'USD')
                        LEFT JOIN pricing_min pmca
                        ON (sailings.sailing_id = pmca.sailing_id AND pmca.currency = 'CAD')
                        LEFT JOIN interim_ports
                        ON sailings.sailing_id = interim_ports.sailing_id
                        WHERE sailings.departure_date > '2017-10-03'
                        and vendors.vendor_id not in(63,1596,1612,71)  #exclude per Elaine
                        ORDER BY sailings.departure_date DESC;";

    /**
     * Update the cruise_all_v2
     */
    public function syncAlgoliaCruiseIndex()
    {
        // Truncate CruiseSearch
        DB::table('cruise_searches')->truncate();

        // Populate CruiseSearch
        DB::select(DB::raw($this->query));

        // Update the index
        $algoliaSync = new AlgoliaSync();
        $algoliaSync->replaceIndex('CruiseSearch', 'cruises_all_v2');
    }
}