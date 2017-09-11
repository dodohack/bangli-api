<?php

namespace App\Http\Controllers\Traits;

trait PaginatorTrait
{
    /**
     * @param $total     - Total number of items
     * @param $pageIdx   - Current page index
     * @param $perPage   - number of items per page
     * @param $realCount - Actually number of items per page
     *
     * @return array contains pagination info
     */
    public function paginator($total = 0, $pageIdx = 0,
                              $perPage = 0, $realCount = 0)
    {
        /* Last available page index */
        $lastPageIdx = floor(($total + $perPage - 1) / $perPage);

        /* Previous and next page index */
        $prePageIdx  = $pageIdx - 1 < 1 ? 1 : $pageIdx - 1;
        $nextPageIdx = $pageIdx + 1 > $lastPageIdx ? $lastPageIdx : $pageIdx + 1;

        return [
            'total'     => $total,
            'count'     => $realCount,
            'cur_page'  => $pageIdx,
            'per_page'  => $perPage,
            'last_page' => $lastPageIdx,
            'pre_page'  => $prePageIdx,
            'next_page' => $nextPageIdx
        ];
    }
}