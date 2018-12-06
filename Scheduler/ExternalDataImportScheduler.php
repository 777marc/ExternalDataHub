<?php

namespace Etg\ETGCustom\ExternalDataHub\Scheduler;

use Carbon\Carbon;
use Etg\Cms\Models\ExternalDataHubParameter;
use Etg\ETGCustom\ExternalDataHub\CommandHandlers\RevelexCruiseDataCommandHandler;
use Etg\ETGCustom\ExternalDataHub\Commands\GetRevelexCruiseData;
use Etg\ETGCustom\ExternalDataHub\Commands\ParseRevelexCruiseData;
use Etg\ETGCustom\ExternalDataHub\PostProcess\AlgoliaOceanCruiseIndex;
use Etg\ETGCustom\ExternalDataHub\PostProcess\AlgoliaRiverCruiseIndex;
use Etg\ETGCustom\ExternalDataHub\PostProcess\CruisePriceMinimum;
use Etg\ETGCustom\ExternalDataHub\PostProcess\CruiseOfferFeed;
use Etg\ETGCustom\ExternalDataHub\PostProcess\RemoveMiscSailings;
use Etg\ETGCustom\ExternalDataHub\PostProcess\UpdatePromosSailings;

/**
 * Class ExternalDataImportScheduler
 * @package Etg\ETGCustom\ExternalDataHub\Scheduler
 */
class ExternalDataImportScheduler
{
    /**
     * Description:  Gets all json files from revelex ftp server
     */
    public function getExternalFiles()
    {
        $rch = new RevelexCruiseDataCommandHandler();
        $com = new GetRevelexCruiseData();

        // US Files
        $rch->getRevelexCruiseData($com,'us');
        // CA Files
        $rch->getRevelexCruiseData($com,'ca');
    }

    /**
     * @param $groupId
     * @param $sailingType
     *
     * Description: Gets groups of sailing data from external_data_hub_parameters table
     * and inserts sailings and itineraries into db
     */
    public function processSailingGroup($groupId, $sailingType)
    {
        $rch = new RevelexCruiseDataCommandHandler();
        $com = new ParseRevelexCruiseData();
        $startTime = Carbon::now();

        // 1. Process Ocean Sailings
        $edhp = ExternalDataHubParameter::where('group_id',$groupId)->
                                          where('type',$sailingType)->get();

        // this can be done better...
        $folder = 'revelex_datafeeds_usd';

        foreach ($edhp as $item)
        {
            $com->fileName = $folder . '\\' . $item->source;
            $com->type = $sailingType;
            $com->vendorId = $item->vendor_id;
            $rch->parseRevelexCruiseData($com);
        }

        if($sailingType == 'ocean_sailing') {
            $itineraryType = 'ocean_itinerary';
        }
        else {
            $itineraryType = 'river_itinerary';
        }

        // 2. Process Ocean Itineraries
        foreach ($edhp as $item)
        {
            $com->fileName = $folder . '\\' . $item->source;
            $com->type = $itineraryType;
            $com->vendorId = $item->vendor_id;
            $rch->parseRevelexCruiseData($com);

            ExternalDataHubParameter::where('vendor_id',$item->vendor_id)->
                                      where('type',$sailingType)->
                                      update(['last_process_date' => Carbon::now()]);
        }

        echo 'start: ' .$startTime . '<br>';
        echo 'end: ' . Carbon::now() . '<br>';
        echo 'sailings and itineraries import complete';
    }

    /**
     * @param $groupId
     * @param $pricingType
     *
     * Description: Gets groups of pricing data from external_data_hub_parameters table
     * and inserts pricing into prices
     */
    public function processPricing($groupId, $pricingType)
    {
        $rch = new RevelexCruiseDataCommandHandler();
        $com = new ParseRevelexCruiseData();
        $startTime = Carbon::now();

        // 1. Process Ocean Pricing
        $edhp = ExternalDataHubParameter::where('group_id',$groupId)->
                                          where('type',$pricingType)->get();

        $folder = 'revelex_datafeeds_usd';

        foreach ($edhp as $item)
        {
            $com->fileName = $folder . '\\' . $item->source;
            $com->type = $pricingType;
            $com->vendorId = $item->vendor_id;
            $com->currency_code = 'USD';
            $rch->parseRevelexCruiseData($com);
        }

        $folder = 'revelex_datafeeds_cad';

        foreach ($edhp as $item)
        {
            $com->fileName = $folder . '\\' . $item->source;
            $com->type = $pricingType;
            $com->vendorId = $item->vendor_id;
            $com->currency_code = 'CAD';
            $rch->parseRevelexCruiseData($com);

            ExternalDataHubParameter::where('vendor_id',$item->vendor_id)->
            where('type',$pricingType)->
            update(['last_process_date' => Carbon::now()]);
        }

        echo 'start: ' .$startTime . '<br>';
        echo 'end: ' . Carbon::now() . '<br>';
        echo 'pricing import complete';

    }

    /**
     * Description: Parses HQ & CAN Promos, inserts into db
     *
     */
    public function processPromos()
    {
        $rch = new RevelexCruiseDataCommandHandler();
        $com = new ParseRevelexCruiseData();
        $startTime = Carbon::now();

        // Process US Promos
        $com->fileName = 'revelex_datafeeds_usd/promotions_agency_ENSEMBLE-HQ.txt';
        $com->type = 'promos';
        $rch->parseRevelexCruiseData($com);

        // Process CA Promos
        $com->fileName = 'revelex_datafeeds_cad/promotions_agency_ENSEMBLE-CAN.txt';
        $com->type = 'promos';
        $rch->parseRevelexCruiseData($com);

        echo 'start: ' .$startTime . '<br>';
        echo 'end: ' . Carbon::now() . '<br>';
        echo 'Promo import complete';
    }

    /**
     * Description: Imports supplemental promo fields
     *
     */
    public function processPromoSupportFields()
    {

        $rch = new RevelexCruiseDataCommandHandler();
        $com = new ParseRevelexCruiseData();
        $startTime = Carbon::now();

        // Process US Promos
        $com->fileName = 'revelex_datafeeds_usd/promotions_agency_ENSEMBLE-HQ.txt';
        $com->type = 'promoSupportFields';
        $rch->parseRevelexCruiseData($com);

        // Process CA Promos
        $com->fileName = 'revelex_datafeeds_cad/promotions_agency_ENSEMBLE-CAN.txt';
        $com->type = 'promoSupportFields';
        $rch->parseRevelexCruiseData($com);

        echo 'start: ' .$startTime . '<br>';
        echo 'end: ' . Carbon::now() . '<br>';
        echo 'Promo import complete';

    }

    /**
     *  Description: Matches promos with sailing records
     *
     */
    public function updateSailingPromos()
    {
        $startTime = Carbon::now();

        $usp = new UpdatePromosSailings();
        $usp->updatePromosOnSailings();

        echo 'start: ' .$startTime . '<br>';
        echo 'end: ' . Carbon::now() . '<br>';
        echo 'Update sailings w/ promos complete';

    }

    /**
     * Description: Syncs Algolia index used for ocean cruise search
     *
     */
    public function syncAlgoliaCruiseIndex($indexType)
    {
        $startTime = Carbon::now();

        switch ($indexType)
        {
            case 'ocean':
                $aoci = new AlgoliaOceanCruiseIndex();
                $aoci->syncAlgoliaCruiseIndex();
            case 'river':
                $arcs = new AlgoliaRiverCruiseIndex();
                $arcs->syncAlgoliaCruiseIndex();
        }

        echo 'start: ' .$startTime . '<br>';
        echo 'end: ' . Carbon::now() . '<br>';
        echo 'Algolia Sync complete';

    }

    /**
     * Description: Updates pricing_min table
     *
     */
    public function updatePricingMinimums()
    {
        $startTime = Carbon::now();

        $cpm = new CruisePriceMinimum();
        $cpm->setMinimumPrices();

        echo 'start: ' .$startTime . '<br>';
        echo 'end: ' . Carbon::now() . '<br>';
        echo 'Price min complete';
    }

    /**
     * Description: Updates pricing_min table
     *
     */
    public function updateCruiseOfferFeed()
    {
        $startTime = Carbon::now();

        $cof = new CruiseOfferFeed();
        $cof->setCruiseOfferFeedData();

        echo 'start: ' .$startTime . '<br>';
        echo 'end: ' . Carbon::now() . '<br>';
        echo 'Cruise offer feed update complete';
    }

    /**
     * Description: Removes unwanted sailings
     *
     */
    public function processMiscRemovals()
    {
        $startTime = Carbon::now();

        $rms = new RemoveMiscSailings();
        $rms->removeSpanishSailings();

        echo 'start: ' .$startTime . '<br>';
        echo 'end: ' . Carbon::now() . '<br>';
        echo 'Cruise offer feed update complete';
    }

    /**
     * Description:  Imports Ocean Ships
     *
     */
    public function processOceanShips()
    {
        $startTime = Carbon::now();
        $rch = new RevelexCruiseDataCommandHandler();
        $com = new ParseRevelexCruiseData();
        $folder = 'revelex_datafeeds_usd';

        // Main Ship Data
        $edhp = ExternalDataHubParameter::where('type','ocean_ships')->get();

        foreach ($edhp as $item)
        {
            $com->fileName = $folder . '\\' . $item->source;
            $com->type = 'ocean_ships';
            $rch->parseRevelexCruiseData($com);
        }

        echo 'start: ' .$startTime . '<br>';
        echo 'end: ' . Carbon::now() . '<br>';
        echo 'ship import complete';
    }

    /**
     * Description: Imports descriptions and ship facts
     *
     */
    public function processOceanShipSupplement()
    {
        $startTime = Carbon::now();
        $rch = new RevelexCruiseDataCommandHandler();
        $com = new ParseRevelexCruiseData();
        $folder = 'revelex_datafeeds_usd';

        // Supplemental Data
        $edhp = ExternalDataHubParameter::where('type','ocean_ship_supplement')->get();
        foreach ($edhp as $item)
        {
            $com->fileName = $folder . '\\' . $item->source;
            $com->type = 'ocean_ship_supplement';
            $rch->parseRevelexCruiseData($com);
        }

        echo 'start: ' .$startTime . '<br>';
        echo 'end: ' . Carbon::now() . '<br>';
        echo 'ship import complete';
    }

    /**
     * Description: Imports extra deck info
     *
     */
    public function processOceanShipDecks()
    {
        $startTime = Carbon::now();
        $rch = new RevelexCruiseDataCommandHandler();
        $com = new ParseRevelexCruiseData();
        $folder = 'revelex_datafeeds_usd';

        // Supplemental Data
        $edhp = ExternalDataHubParameter::where('type','ocean_ship_supplement')->get();
        foreach ($edhp as $item)
        {
            $com->fileName = $folder . '\\' . $item->source;
            $com->type = 'ocean_ship_decks';
            $rch->parseRevelexCruiseData($com);
        }

        echo 'start: ' .$startTime . '<br>';
        echo 'end: ' . Carbon::now() . '<br>';
        echo 'ship import complete';
    }

    /**
     * Description: Imports extra deck category info
     *
     */
    public function processOceanShipCategories()
    {
        $startTime = Carbon::now();
        $rch = new RevelexCruiseDataCommandHandler();
        $com = new ParseRevelexCruiseData();
        $folder = 'revelex_datafeeds_usd';

        // Supplemental Data
        $edhp = ExternalDataHubParameter::where('type','ocean_ship_supplement')->get();
        foreach ($edhp as $item)
        {
            $com->fileName = $folder . '\\' . $item->source;
            $com->type = 'ocean_ship_categories';
            $rch->parseRevelexCruiseData($com);
        }

        echo 'start: ' .$startTime . '<br>';
        echo 'end: ' . Carbon::now() . '<br>';
        echo 'ship import complete';
    }

}