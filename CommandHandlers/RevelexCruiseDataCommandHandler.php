<?php

namespace Etg\ETGCustom\ExternalDataHub\CommandHandlers;

use Etg\ETGCustom\ExternalDataHub\Commands\GetRevelexCruiseData;
use Etg\ETGCustom\ExternalDataHub\Commands\ParseRevelexCruiseData;
use Etg\ETGCustom\ExternalDataHub\FileManager\EtgFtp;
use Etg\ETGCustom\ExternalDataHub\FileProcess\ProcessSailingData;
use Etg\ETGCustom\ExternalDataHub\FileProcess\ProcessItineraryData;
use Etg\ETGCustom\ExternalDataHub\FileProcess\ProcessPricingData;
use Etg\ETGCustom\ExternalDataHub\FileProcess\ProcessPromoData;
use Etg\ETGCustom\ExternalDataHub\FileProcess\ProcessShipData;

/**
 * Class RevelexCruiseDataCommandHandler
 * @package Etg\ETGCustom\ExternalDataHub\CommandHandler
 */
class RevelexCruiseDataCommandHandler
{
    /**
     * @param GetRevelexCruiseData $command
     * @param $region
     */
    public function getRevelexCruiseData(GetRevelexCruiseData $command, $region)
    {
        $ftp = new EtgFtp();

        switch ($region)
        {
            case ('us'):
                $ftp->getFile($command->FTPConnection,$command->USDirectory);
                break;
            case ('ca'):
                $ftp->getFile($command->FTPConnection,$command->CADirectory);
                break;
        }
    }

    /**
     * @param ParseRevelexCruiseData $command
     */
    public function parseRevelexCruiseData(ParseRevelexCruiseData $command)
    {
        switch ($command->type)
        {
            case 'ocean_sailing':
                $psd = new ProcessSailingData();
                $psd->insertOceanSailings($command->vendorId, $command->fileName);
                break;
            case 'river_sailing':
                $psd = new ProcessSailingData();
                $psd->insertRiverSailings($command->vendorId, $command->fileName);
                break;
            case 'ocean_itinerary':
                $psd = new ProcessItineraryData();
                $psd->insertOceanItineraries($command->vendorId, $command->fileName);
                break;
            case 'river_itinerary':
                $psd = new ProcessItineraryData();
                $psd->insertRiverItineraries($command->vendorId, $command->fileName);
                break;
            case 'ocean_pricing':
            case 'river_pricing':
                $psd = new ProcessPricingData();
                $psd->insertPricing($command->vendorId,$command->fileName,$command->currency_code);
                break;
            case 'promos':
                $psd = new ProcessPromoData();
                $psd->insertPromoData($command->fileName);
                break;
            case 'promoSupportFields':
                $psd = new ProcessPromoData();
                $psd->insertPromoDataSupportFields($command->fileName);
                break;
            case 'ocean_ships':
                $psd = new ProcessShipData();
                $psd->insertOceanShips($command->fileName);
                break;
            case 'ocean_ship_supplement':
                $psd = new ProcessShipData();
                $psd->insertOceanShipsSupplement($command->fileName);
                break;
            case 'ocean_ship_decks':
                $psd = new ProcessShipData();
                $psd->insertOceanShipDecksSupplemental($command->fileName);
                break;
            case 'ocean_ship_categories':
                $psd = new ProcessShipData();
                $psd->insertOceanShipCategorySupplemental($command->fileName);
                break;
        }
    }
}