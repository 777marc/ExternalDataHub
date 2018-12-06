<?php

namespace Etg\ETGCustom\ExternalDataHub\FileManager;

/**
 * Interface FTPOperationsInterface
 * @package Etg\ETGCustom\ExternalDataHub\FileManager
 */
interface FTPOperationsInterface
{
    /**
     * @param $conn
     * @param $destination
     * @return mixed
     */
    public function getFile($conn, $destination);

    /**
     * @param $conn
     * @param $destination
     * @return mixed
     */
    public function putFile($conn, $destination);
}