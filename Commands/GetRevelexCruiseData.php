<?php
/**
 * Created by PhpStorm.
 * User: Marc
 * Date: 11/9/2017
 * Time: 3:01 PM
 */

namespace Etg\ETGCustom\ExternalDataHub\Commands;

/**
 * Class GetRevelexCruiseData
 * @package Etg\ETGCustom\ExternalDataHub\Commands
 */
class GetRevelexCruiseData
{

    /**
     * @var string
     */
    public $FTPConnection = 'revelex';

    /**
     * @var string
     */
    public $USDirectory = 'revelex_datafeeds_usd';

    /**
     * @var string
     */
    public $CADirectory = 'revelex_datafeeds_cad';

}