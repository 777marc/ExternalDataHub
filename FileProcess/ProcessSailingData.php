<?php

namespace Etg\ETGCustom\ExternalDataHub\FileProcess;

use Illuminate\Support\Facades\Storage;
use Etg\Cms\Models\Sailing;
use Etg\Cms\Models\RiverSailing;

/**
 * Class ProcessSailingData
 * @package Etg\ETGCustom\ExternalDataHub\FileProcess
 */
class ProcessSailingData
{
    /**
     * @param $vendorId
     * @param $dataSource
     */
    public function insertOceanSailings($vendorId, $dataSource)
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
        Sailing::where('vendor_id',$vendorId)->delete();

        $bulkInsertArray = [];

        foreach ($json_a['Dataset']as $item)
        {
            array_push($bulkInsertArray,array('vendor_id' => $vendorId, 'sailing_id' => $item["SailingID"], 'sailing_plan_code' => $item["SailingPlanCode"],
                                              'market_name' => $item["MarketName"], 'ship_id' => $item["ShipID"], 'ship_name' => $item["ShipName"],
                                              'departure_date' => $item["DepartureDate"], 'duration' => $item["Duration"], 'package_id' => $item["PackageID"],
                                              'sailing_name' => $item["SailingName"], 'destination_id' => $item["DestinationID"], 'departure_port_code' => $item["DeparturePortCode"],
                                              'departure_port_id' => $item["DeparturePortID"], 'return_port_code' => $item["ReturnPortCode"],'return_port_id' => $item["ReturnPortID"],
                                              'package_type_id' => $item["PackageTypeID"], 'tour_only' => $item["TourOnly"], 'segment' => $item["Segment"]));
        }

        Sailing::insert($bulkInsertArray);
    }

    /**
     * @param $vendorId
     * @param $dataSource
     */
    public function insertRiverSailings($vendorId, $dataSource)
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
        RiverSailing::where('vendor_id',$vendorId)->delete();

        $bulkInsertArray = [];

        foreach ($json_a['Dataset']as $item)
        {

            $shipId = ($item["ShipID"] = null ? $item["ShipID"] : 0);
            $marketName = ($item["MarketName"] = null ? $item["MarketName"] : ' ');

            array_push($bulkInsertArray,array('vendor_id' => $vendorId, 'sailing_id' => $item["SailingID"], 'sailing_plan_code' => $item["SailingPlanCode"],
                                              'market_name' => $marketName, 'ship_id' => $shipId, 'departure_date' => $item["DepartureDate"],
                                              'duration' => $item["Duration"], 'package_id' => $item["PackageID"],
                                              'sailing_name' => $item["SailingName"], 'destination_id' => $item["DestinationID"],
                                              'package_type_id' => $item["PackageTypeID"]));
        }

        RiverSailing::insert($bulkInsertArray);

    }

}
