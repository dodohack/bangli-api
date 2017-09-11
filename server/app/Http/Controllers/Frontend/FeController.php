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
        $result = $this->getArrayEntities($inputs['etype'], $inputs,
            $relations, $columns, $pagination);

        $result['key'] = $inputs['key'];

        return $result;
    }
}