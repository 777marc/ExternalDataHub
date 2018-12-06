<?php

namespace Etg\ETGCustom\ExternalDataHub\PostProcess;

use Illuminate\Support\Facades\DB;

/**
 * Class RemoveMiscSailings
 *
 * Description: this can be sued for any misc clean up
 *
 * @package Etg\ETGCustom\ExternalDataHub\PostProcess
 */
class RemoveMiscSailings
{
    private $querySpanishSailings = "DELETE FROM sailings WHERE market_name = '[Spain, Portugal]';";

    /**
     * Description: Removes certain Spanish title sailings because they don't apply
     */
    public function removeSpanishSailings()
    {
        // Remove sailings
        DB::select(DB::raw($this->querySpanishSailings));
    }

}