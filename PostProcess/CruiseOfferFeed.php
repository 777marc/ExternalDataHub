<?php

namespace Etg\ETGCustom\ExternalDataHub\PostProcess;

use Illuminate\Support\Facades\DB;

/**
 * Class CruiseOfferFeed
 * @package Etg\ETGCustom\ExternalDataHub\PostProcess
 */
class CruiseOfferFeed
{
    /**
     * @var string
     */
    private $preQuery = "set group_concat_max_len = 4294967295;";

    /**
     * @var string
     */
    private $query = "insert into feed_cruise_offers (sailing_id, vendor_id, sub_id, sailing_name, vendor_name, ship_id, ship_name, duration, is_hosted, is_agent_exclusive, departure_date, promo_callout, booking_instructions, consumer_disclaimer, start_date, end_date, vendor_code, itinerary, ship_image)
                      SELECT sailings.sailing_id,
                               sailings.vendor_id,
                               sailings.sailing_id AS 'sub_id',
                               sailing_name,
                               vendors.name AS 'vendor_name',
                               sailings.ship_id,
                               sailings.ship_name,
                               sailings.duration,
                               CASE WHEN sailings.promo_callout LIKE '%Hosted%' THEN 'Hosted' ELSE 'none' END as 'is_hosted',
                               CASE WHEN sailings.promo_callout LIKE '%Hosted%' THEN 'False' ELSE 'True' END as 'is_agent_exclusive',
                               DATE_FORMAT(sailings.departure_date,'%m/%d/%Y') AS 'departure_date',
                               REPLACE(sailings.promo_callout,'&nbsp;',' ') AS 'promo_callout',
                               strip_tags(REPLACE(b2btext.text,'&nbsp;',' ')) AS 'booking_instructions',
                               strip_tags(REPLACE(b2ctext.text,'&nbsp;',' ')) AS 'consumer_disclaimer',
                               promotion_dates.start_date,
                               promotion_dates.end_date,
                               vendors.code AS 'vendor_code',
                               (SELECT GROUP_CONCAT(CONCAT(day_number,'~',port_code,'~',arrival_time,'~',departure_time,'~',IFNULL(port_name,'-'))SEPARATOR ';')
                                FROM itineraries WHERE sailing_plan_id = sailings.sailing_id) AS 'itinerary',
                               ships.image_path AS 'ship_image'
                        FROM sailings
                        JOIN vendors
                        ON sailings.vendor_id = vendors.vendor_id
                        LEFT JOIN promotion_texts b2btext
                        ON (sailings.promotion_id = b2btext.promotion_id and b2btext.text_type = 'B2B')
                        LEFT JOIN promotion_texts b2ctext
                        ON (sailings.promotion_id = b2ctext.promotion_id and b2ctext.text_type = 'B2C')
                        LEFT JOIN promotion_dates
                        ON sailings.promotion_id = promotion_dates.promotion_id
                        LEFT JOIN ships
                        ON sailings.ship_id = ships.ship_id
                        WHERE sailings.promotion_id IS NOT NULL
                        AND sailings.departure_date > CURRENT_TIMESTAMP()
                        GROUP BY sailing_id;";

    /**
     * Description: Seeds feed_cruise_offers table used by the offer feed service
     */
    public function setCruiseOfferFeedData()
    {
        // Truncate feed_cruise_offers
        DB::table('feed_cruise_offers')->truncate();

        // Set concat max length
        DB::select(DB::raw($this->preQuery));

        // Populate feed_cruise_offers
        DB::select(DB::raw($this->query));
    }

}