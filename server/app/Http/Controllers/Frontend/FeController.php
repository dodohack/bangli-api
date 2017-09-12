<?php
/**
 * Frontend controller, base controller of all frontend controllers
 */

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Frontend\Offer;


class FeController extends Controller
{

    /**
     * Return a list of published entities indexed by given key
     * @param Request $request
     * @param $relations
     * @param $columns
     * @param string $pagination
     * @return mixed
     */
    public function getEntitiesByKey(Request $request, $relations, $columns,
                                     $pagination = 'full')
    {
        $result = $this->getArrayEntitiesByKey($request->all(), $relations,
            $columns, $pagination);

        return $this->success($request, json_encode($result));
    }

    /**
     * Return an array of published entities indexed by given key
     * @param $inputs
     * @param $relations
     * @param $columns
     * @param $pagination
     */
    public function getArrayEntitiesByKey($inputs, $relations, $columns,
                                          $pagination)
    {
        // TODO: should always query entity with 'publish' status.
        $result = $this->getArrayEntities($inputs['etype'], $inputs,
            $relations, $columns, $pagination);

        $result['key'] = $inputs['key'];

        return $result;
    }

    /**
     * Overload parent function, return frontend specific models
     * @param $etype
     */
    protected function getEntityTable($etype)
    {
        // TODO: Add more frontend only models
        switch ($etype) {
            case ETYPE_OFFER:       return new Offer;
            default:
                return parent::getEntityTable($etype);
        }
    }

    /**
     * Overload parent function, returns table name in literal string
     * @param $etype
     * @return string
     */
    protected function getTableName($etype)
    {
        switch ($etype) {
            case ETYPE_OFFER:        return 'offers';
            default:
                return parent::getTableName($etype);
        }
    }
}