<?php

namespace Etg\ETGCustom\ExternalDataHub\FileManager;

use Anchu\Ftp\Facades\Ftp;
use Illuminate\Support\Facades\Storage;
use Etg\Cms\Models\ExternalDataHubParameter;

/**
 * Class EtgFtp
 * @package Etg\ETGCustom\ExternalDataHub\FileManager
 *
 * Desc
 */
class EtgFtp implements FTPOperationsInterface
{
    /**
     * @param $conn
     * @param $destination
     * @return bool
     */
    public function getFile($conn, $destination)
    {
        $status = Ftp::connection($conn)->changeDir($destination);

        if(!$status)
        {
            dd('unable to connect to ftp server');
        }

        // Get a list of files to download
        $edhp = ExternalDataHubParameter::whereNotNull('source')->pluck('source')->toArray();;

        $listing = Ftp::connection($conn)->getDirListing();

        // Check for directories
        $path = $destination . '/';

        for($i=0; $i<count($listing); $i++)
        {
            if(in_array($listing[$i],$edhp))
            {
                $file = Ftp::connection($conn)->readfile($listing[$i]);
                Storage::disk('local')->put($path . $listing[$i], $file);
            }
        }

        FTP::disconnect($conn);

        return true;
    }

    /**
     * @param $conn
     * @param $destination
     * @return bool
     */
    public function putFile($conn, $destination)
    {
        // FOR FUTURE USE MAYBE
        return true;
    }
}