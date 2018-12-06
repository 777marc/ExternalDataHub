<?php

namespace Etg\ETGCustom\ExternalDataHub\FileProcess;

use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Etg\Cms\Models\PromotionText;
use Illuminate\Support\Facades\DB;

/**
 * Class ProcessPromoData
 * @package Etg\ETGCustom\ExternalDataHub\FileProcess
 */
class ProcessPromoData
{

    /**
     * @param $dataSource
     * @throws \Exception
     */
    public function insertPromoData($dataSource)
    {
        // get data
        $string = Storage::get($dataSource);
        $json_a = json_decode($string, true);

        // set max execution time
        ini_set('max_execution_time', 1200);

        // clean up old data
        DB::table('promotion_texts')->truncate();

        foreach ($json_a['Dataset']as $item)
        {
            // do not import expired promotions
            $arr = array($item['DateApplicability'][0]);
            $skip = false;
            foreach ($arr as $key => $itm) {
                if ($key == 'EndDate') {
                    $promoEndDate = Carbon::parse($itm['EndDate']);
                    if($promoEndDate <= Carbon::today()) {
                        $skip = true;
                    }
                }
            }

            if($skip) {
                continue;
            }

            // Get sailing ID
            $promoId = $item['PromotionID'];
            $sailing_id = 0;
            $arr2 = array($item['PromotionField']);
            foreach ($arr2[0] as $itm2) {
                foreach ($itm2 as $key => $subItm){
                    if($key === 'Sailings'){
                        $sailing_id = $subItm;
                    }
                }
            }

            // Insert
            foreach ($item['PromotionText'] as $itm){
                $insertArray = array('promotion_id' => $promoId, 'text_type' => $itm['TextType'],
                                      'language_code' => $itm['LanguageCode'], 'text' => $itm['Text'],
                                      'allow_override' => $itm['AllowOverride'], 'sailing_id' => $sailing_id);

                PromotionText::insert($insertArray);
            }
        }
    }

    /**
     * @param $dataSource
     */
    public function insertPromoDataSupportFields($dataSource)
    {

        // get data
        $string = Storage::get($dataSource);
        $json_a = json_decode($string, true);

        // set max execution time
        ini_set('max_execution_time', 1200);

        // clean up old data
        DB::table('promotion_texts')->truncate();

        $bulkArray = [];

        foreach ($json_a['Dataset']as $item) {

            dd($item['PromotionField']);

            foreach ($item['PromotionField'] as $fld){

                array_push($bulkArray,array());


            }

        }

    }
}