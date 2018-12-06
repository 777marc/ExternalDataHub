<?php

namespace Etg\ETGCustom\ExternalDataHub\FileProcess;

use Illuminate\Support\Facades\Storage;
use Etg\Cms\Models\Itinerary;
use Etg\Cms\Models\RiverItinerary;

/**
 * Class ProcessItineraryData
 * @package Etg\ETGCustom\ExternalDataHub\FileProcess
 */
class ProcessItineraryData
{
    /**
     * @param $vendorId
     * @param $dataSource
     *
     * Description: This inserts pricing records 1 thousand records at a time
     */
    public function insertOceanItineraries($vendorId, $dataSource)
    {
        // get data
        $string = Storage::get($dataSource);
        $json_a = json_decode($string, true);

        // delete current
        Itinerary::where('vendor_id',$vendorId)->delete();

        $bulkArray = [];

        foreach ($json_a['Dataset']as $item)
        {
            foreach ($item['Itinerary'] as $itinItem)
            {
                array_push($bulkArray,array('vendor_id' => $vendorId, 'sailing_plan_id' => $item['SailingID'], 'day_number' => $itinItem['DayNumber'],
                                            'port_id' => $itinItem['PortID'], 'port_code' => $itinItem['PortCode'],'description' => $itinItem['Description'],
                                            'arrival_time' => $itinItem['ArrivalTime'], 'departure_time' => $itinItem['DepartureTime']));
            }

            // If you're the same sailing and you've reached 1000 itineraries, commit
            if(count($bulkArray) >= 1000)
            {
                Itinerary::insert($bulkArray);
                $bulkArray = [];
            }
        }
        Itinerary::insert($bulkArray);
    }

    /**
     * @param $vendorId
     * @param $dataSource
     */
    public function insertRiverItineraries($vendorId, $dataSource)
    {
        // get data
        $string = Storage::get($dataSource);
        $json_a = json_decode($string, true);

        // delete current
        RiverItinerary::where('vendor_id',$vendorId)->delete();

        $bulkArray = [];

        foreach ($json_a['Dataset']as $item)
        {
            foreach ($item['Itinerary'] as $itinItem)
            {
                array_push($bulkArray,array('sailing_plan_id' => $item['SailingID'], 'day_number' => $itinItem['DayNumber'],
                                            'description' => $itinItem['Description'], 'arrival_time' => $itinItem['ArrivalTime'],
                                            'departure_time' => $itinItem['DepartureTime'], 'vendor_id' => $vendorId));
            }

            // If you're the same sailing and you've reached 1000 itineraries, commit
            if(count($bulkArray) >= 1000)
            {
                RiverItinerary::insert($bulkArray);
                $bulkArray = [];
            }
        }
        RiverItinerary::insert($bulkArray);
    }
}