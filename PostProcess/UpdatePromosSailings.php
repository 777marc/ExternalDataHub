<?php

namespace Etg\ETGCustom\ExternalDataHub\PostProcess;

use Illuminate\Support\Facades\DB;

/**
 * Class UpdatePromosSailings
 * @package Etg\ETGCustom\ExternalDataHub\PostProcess
 */
class UpdatePromosSailings
{
    private $query1 = "INSERT into promo_temp (sailing_id, promotion_id, text, plain_text)
                      SELECT b.field_value as 'sailing_id',
                             max(a.promotion_id) as 'promotion_id',
                             max(a.text) as 'text',
                             max(strip_tags(a.text)) as 'promo_text'
                       FROM core.promotion_texts a
                       JOIN core.promotion_fields b
                       ON (a.promotion_id = b.promotion_id AND b.field_name = 'Sailings')
                       WHERE a.text_type = 'callout'
                       GROUP BY b.field_value;";
                      
    private $query2 = "UPDATE sailings
                       SET promotion_id = NULL,
                           promotion_text = NULL,
                           promo_callout = NULL;";
                    
    private $query3 = "UPDATE sailings a
                       JOIN promo_temp b ON a.sailing_id = b.sailing_id
                       SET a.promotion_id = b.promotion_id,
                           a.promotion_text = b.text,
                           a.promo_callout = b.plain_text,
                           a.departure_date = a.departure_date;";

    /**
     * Description: Updates promo info on associated sailings
     * where sailing_id's match
     *
     */
    public function updatePromosOnSailings()
    {
        // Truncate pricing_min
        DB::table('promo_temp')->truncate();

        DB::select(DB::raw($this->query1));
        DB::select(DB::raw($this->query2));
        DB::select(DB::raw($this->query3));
    }

}