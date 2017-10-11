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
     * Return an array of published entities indexed by given key
     * @param $inputs
     * @param $relations
     * @param $relCount
     * @param $columns
     * @param $pagination
     * @return array of entities indexed by key
     */
    public function getArrayEntitiesByKey($inputs, $relations, $relCount,
                                          $columns, $pagination)
    {
        // Alway query published entities for frontend.
        $inputs['status'] = 'publish';

        // TODO: should always query entity with 'publish' status.
        $result = $this->getArrayEntities($inputs['etype'], $inputs,
            $relations, $relCount, $columns, $pagination);

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
    public function getGroupedEntities($inputs, $relations, $columns)
    {
        // Alway query published entities for frontend.
        $inputs['status'] = 'publish';

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
        return ['etype' => $etype, 'data' => $result];
    }

    /**
     * Sanitize incoming relations string, return relations in array
     * FIXME: Hardcoded relationship
     * @param $relationString
     * @return array
     */
    protected function setupRelations($relationString) {
        $relations = [];
        if (!$relationString) return null;

        // We expect incoming relations are separated by ','
        $tokens = explode(",", $relationString);
        foreach ($tokens as $rel) {
            switch($rel) {
                case ETYPE_TOPIC:
                    array_push($relations, 'topics');
                    break;
                case ETYPE_OFFER:
                    array_push($relations, 'offers');
                    break;
                case ETYPE_POST:
                    array_push($relations, 'posts');
                    break;
                case ETYPE_PAGE:
                    array_push($relations, 'pages');
                    break;
                case ETYPE_ATTACHMENT:
                    array_push($relations, 'attachments');
                    break;
                case ETYPE_COMMENT:
                    array_push($relations, 'comments');
                    break;
            }
        }

        if (count($relations))
            return $relations;
        else
            return null;
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

    // FIXME: We are going to remove fe_view_* as it casued so much troubles
    // and performance issue as well probably!

    /**
     * Overload parent function, returns table name in literal string
     * @param $etype
     * @return string
     */
    /*
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
    */
}