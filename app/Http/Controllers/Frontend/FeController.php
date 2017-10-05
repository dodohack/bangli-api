<?php
/**
 * Frontend controller, base controller of all frontend controllers
 */

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\EntityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\FeTopic;
use App\Models\FePost;
use App\Models\FePage;
use App\Models\FeOffer;
use App\Models\FeAdvertise;
use App\Models\FeComment;

class FeController extends EntityController
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
            $relations, null, $columns, $pagination);

        $result['key'] = $inputs['key'];

        return $result;
    }

    /**
     * Return single/multiple grouped of entities with filter key as index
     * of each group.
     * @param Request $request
     * @param $relations
     * @param $columns
     * @return string
     */
    public function getGroupedEntities(Request $request, $relations, $columns)
    {
        $inputs = $request->all();
        $etype  = $inputs['etype'];
        $isFullPagination = isset($inputs['pagination']) ? true : false;

        unset($inputs['etype'], $inputs['pagination']);

        $result = [];

        // We expect only grouped entity query parameters reach here.
        foreach ($inputs as $key => $paramStr) {
            // Decode the string
            $params = $this->decodeParams($paramStr);

            // FIXME: By using fe_view_*, we actually do not need this, but
            // can we call child method from parent method?
            $params['status'] = 'publish';

            // Get entities for each group
            $result[$key] = $this->getArrayEntities($etype, $params,
                $relations, null, $columns, $isFullPagination);
        }

        // Return entities with 'etype' in top level
        $ret = ['etype' => $etype, 'data' => $result];

        return $this->success($request, json_encode($ret));
    }

    /**
     * Overload parent function, return frontend specific models
     * @param $etype
     */
    protected function getEntityTable($etype)
    {
        switch ($etype) {
            case ETYPE_TOPIC:       return new FeTopic;
            case ETYPE_POST:        return new FePost;
            case ETYPE_OFFER:       return new FeOffer;
            case ETYPE_PAGE:        return new FePage;
            case ETYPE_ADVERTISE:   return new FeAdvertise;
            case ETYPE_COMMENT:     return new FeComment;
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
            case ETYPE_TOPIC:        return 'fe_view_topics';
            case ETYPE_POST:         return 'fe_view_posts';
            case ETYPE_PAGE:         return 'fe_view_pages';
            case ETYPE_OFFER:        return 'fe_view_offers';
            case ETYPE_ADVERTISE:    return 'fe_view_advertisers';
            case ETYPE_COMMENT:      return 'fe_view_comments';
            default:
                return parent::getTableName($etype);
        }
    }
}