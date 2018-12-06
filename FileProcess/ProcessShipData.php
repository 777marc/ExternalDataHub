<?php

namespace Etg\ETGCustom\ExternalDataHub\FileProcess;

use Illuminate\Support\Facades\Storage;
use Etg\Cms\Models\Ship;
use Etg\Cms\Models\ShipDeck;
use Etg\Cms\Models\ShipCategory;
use Illuminate\Support\Facades\DB;

/**
 * Class ProcessShipData
 * @package Etg\ETGCustom\ExternalDataHub\FileProcess
 */
class ProcessShipData
{
    /**
     * @param $dataSource
     */
    public function insertOceanShips($dataSource)
    {
        // get data
        $exists = Storage::disk('local')->exists($dataSource);
        if(!$exists)
        {
            dd('unable to find json file ' . $dataSource);
        }

        $string = Storage::get($dataSource);
        $json_a = json_decode($string, true);

        // delete current
        DB::table('ships')->truncate();
        DB::table('ship_decks')->truncate();
        DB::table('ship_categories')->truncate();

        $bulkShipArray = [];

        foreach ($json_a['Dataset']as $item)
        {
            if(isset($item['VendorID'])) {
                // main ship info
                array_push($bulkShipArray, array('ship_id' => $item['ShipID'], 'vendor_id' => $item['VendorID'],
                    'code' => $item['Code'], 'name' => $item['Name'], 'image_path' => $item['ImagePath'],
                    'max_occupancy' => $item['MaxOccupancy'],'max_adults' => $item['MaxAdults'],'max_children' => $item['MaxChildren'],
                    'max_infants' => $item['MaxInfants'],'max_seniors' => $item['MaxSeniors'],'has_tours' => $item['HasTours'],
                    'tour_only' => $item['TourOnly'],'has_river' => $item['HasRiver'],'river_only' => $item['RiverOnly'],
                    'market_name' => $item['MarketName'],'adult_max_age' => $item['AdultMaximumAge'],'adult_min_age' => $item['AdultMinimumAge'],
                    'child_max_age' => $item['ChildMaximumAge'],'child_min_age' => $item['ChildMinimumAges'],'infant_max_age' => $item['InfantMaximumAge'],
                    'infant_max_age_units' => $item['InfantMaximumAgeUnits'],'infant_min_age' => $item['InfantMinimumAge'],
                    'infant_min_age_units' => $item['InfantMinimumAgeUnits'],'min_supervisor_age' => $item['MinimumSupervisorAge'],
                    'senior_max_age' => $item['SeniorMaximumAge'],'senior_min_age' => $item['SeniorMinimumAge']));

            }

            // Deck info
            $bulkDeckArray = [];
            if(isset($item['DeckDataset']))
            {
                foreach ($item['DeckDataset'] as $deckItm)
                {
                    array_push($bulkDeckArray, array('ship_id' => $item['ShipID'],'deck_id' => $deckItm['DeckID'],'deck_number' => $deckItm['DeckNumber'],
                        'name' => $deckItm['Name'],'display_cabins' => $deckItm['DisplayCabins'],'start_date' => $deckItm['StartDate'],
                        'end_date' => $deckItm['EndDate'],'image_path' => $deckItm['ImagePath']));
                }
                ShipDeck::insert($bulkDeckArray);
            }


            // Category Info
            $bulkCatArray = [];
            if(isset($item['CategoryDataset']))
            {
                foreach ($item['CategoryDataset'] as $catItm)
                {
                    array_push($bulkCatArray, array('ship_id' => $item['ShipID'],'category_id' => $catItm['CategoryID'],'code' => $catItm['Code'],
                        'cabin_type' => $catItm['CabinType'],'start_date' => $catItm['StartDate'],'end_date' => $catItm['EndDate'],
                        'image_path' => $catItm['ImagePath']));
                }
                ShipCategory::insert($bulkCatArray);
            }
        }

        Ship::insert($bulkShipArray);

    }

    /**
     * @param $dataSource
     */
    public function insertOceanShipsSupplement($dataSource)
    {
        // get data
        $exists = Storage::disk('local')->exists($dataSource);
        if (!$exists) {
            dd('unable to find json file ' . $dataSource);
        }

        $string = Storage::get($dataSource);
        $json_a = json_decode($string, true);

        foreach ($json_a['Dataset'] as $item) {

            switch ($item['Title'])
            {
                case 'Ship Facts':
                    Ship::where('ship_id',$item['ShipID'])->update(['ship_facts' => $item['Text']]);
                    break;
                case 'Summary':
                    Ship::where('ship_id',$item['ShipID'])->update(['description' => $item['Text']]);
                    break;
            }

            if(isset($item['DeckDataset']))
            {
                foreach ($item['DeckDataset'] as $deckItm)
                {
                    ShipDeck::where('deck_id',$deckItm['DeckID'])->update(['name' => $deckItm['DisplayName']]);
                }
            }

            if(isset($item['CategoryDataset']))
            {
                foreach ($item['CategoryDataset'] as $catItm)
                {
                    ShipCategory::where('category_id',$catItm['CategoryID'])->update(['description' => $catItm['Description']]);
                }
            }
        }
    }

    /**
     * @param $dataSource
     */
    public function insertOceanShipDecksSupplemental($dataSource)
    {
        // get data
        $exists = Storage::disk('local')->exists($dataSource);
        if (!$exists) {
            dd('unable to find json file ' . $dataSource);
        }

        $string = Storage::get($dataSource);
        $json_a = json_decode($string, true);

        foreach ($json_a['Dataset'] as $item) {

            if(isset($item['DeckDataset']))
            {
                foreach ($item['DeckDataset'] as $deckItm)
                {
                    ShipDeck::where('deck_id',$deckItm['DeckID'])->update(['name' => $deckItm['DisplayName']]);
                }
            }

        }

    }

    /**
     * @param $dataSource
     */
    public function insertOceanShipCategorySupplemental($dataSource)
    {
        // get data
        $exists = Storage::disk('local')->exists($dataSource);
        if (!$exists) {
            dd('unable to find json file ' . $dataSource);
        }

        $string = Storage::get($dataSource);
        $json_a = json_decode($string, true);

        foreach ($json_a['Dataset'] as $item) {

            if(isset($item['CategoryDataset']))
            {
                foreach ($item['CategoryDataset'] as $catItm)
                {
                    ShipCategory::where('category_id',$catItm['CategoryID'])->update(['description' => $catItm['Description']]);
                }
            }
        }

    }

}