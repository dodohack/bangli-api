<?php

/**
 * A base controller, many other controllers extend from this, it shares many
 * common methods.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Traits\EntityFilterTrait;
use App\Http\Controllers\Traits\PaginatorTrait;
use App\Http\Controllers\Traits\ContentFilterTrait;
use App\Models\Post;
use App\Models\Page;
use App\Models\Topic;
use App\Models\Offer;
use App\Models\Advertise;
use App\Models\Newsletter;
use App\Models\Attachment;
use App\Models\Comment;


class EntityController extends Controller
{
    use EntityFilterTrait, PaginatorTrait, ContentFilterTrait;

    // Table relations, count of relation and columns to be retrieved
    protected $relations;
    protected $relCount;
    protected $columns;

    // The pagination of returned entities, supports:
    // 'full'   - full pagination with num of pages etc
    // 'simple' - simple pagination with pre and next
    // 'none'   - no pagination
    protected $pagination;

    // Input filters, different entities use some of them
    protected $status;    // Entity status
    protected $author;    // Entity author if applicable
    protected $editor;    // Entity editor if applicable
    protected $perPage;   // Number of entities per list page
    protected $curPage;   // Current page of entity list
    protected $params;    // Entity specific filters
    // FIXME: Resolve these 2 date filter
    protected $fData;     // Date filter
    protected $date;      // Date filter
    protected $eType;     // Entity type
    protected $eId;       // Entity relation id ???
    protected $query;     // User input search string
    protected $skipNum;   // Number of skipped records of current page
    protected $category;
    protected $featured;
    protected $topic;
    protected $topicType;
    protected $topicHasOffer;
    protected $topicHasFeaturedOffer;
    protected $topicGuidStarts;

    protected $position;  // Advertise position

    // Sorting
    protected $orderBy;   // Order by 'table column name'
    protected $order;     // Sort by 'desc' or 'asc'

    public function __construct(Request $request)
    {
        parent::__construct($request);
        if ($request)
            $this->etype = $request->get('etype');
    }

    /**
     * Get the guard to be used during authentication.
     * @return mixed
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * Get an array of entities matches given filters
     * @param $inputs
     * @param null $relations
     * @param null $relCount
     * @param null $columns
     * @param string $pagination
     * @return array | bool
     */
    protected function getEntities($inputs,
                                   $relations = null,
                                   $relCount = null,
                                   $columns = null,
                                   $pagination = 'full')
    {

        // Client side can control with columns and relationship to retrieve
        if (array_key_exists("columns", $inputs) && !$columns) {
            if ($inputs['columns'] != '')
                $columns = explode(',', $inputs['columns']);
            else
                $columns = null;
        }

        if (array_key_exists("relations", $inputs) && !$relations) {
            if ($inputs['relations'] != '')
                $relations = explode(',', $inputs['relations']);
            else
                $relations = null;
        }

        $this->pagination = $pagination;

        // Setup db query parameters
        $this->initInputFilters($inputs);

        // Decide which table to query
        $table = $this->getEntityTable();
        if (!$table)  return false;

        // Decide the table name
        $tableName = $this->getTableName();

        // Decide columns and relations to be queried with this table
        $this->setupColumns($table, $columns, false);
        $this->setupRelations($table, $relations, $relCount, false);

        // 1. Filter entities
        $table = $this->filterEntities($table, $tableName);

        // 2. Run the actually query
        $res = $this->getEntitiesInternal($table);

        $total    = $res['total'];
        $entities = $res['entities'];

        $ret = [ 'entities' => $entities->toArray() ];

        // Return entities w/wo pagination
        if ($this->pagination != 'none') {
            $paginator = $this->paginator($total, $this->curPage,
                $this->perPage, $entities->count());

            $ret['paginator'] = $paginator;
        }

        // Return array of entities
        return $ret;
    }

    /**
     * Get an entity object with it's relations
     * @param $key - 'id' for post or 'guid' topic
     * @param $table - table to query, optional
     * @param $relations - entity relation tables to be queried
     * @param $columns - entity table columns to be queried
     * @return array | boolean
     */
    protected function getEntity($key, $id,
                                 $table = null,
                                 $relations = null,
                                 $columns = null,
                                 $relCount = null)
    {
        if (!$table) {
            $db = $this->getEntityTable();
            if (!$db) return false;
        } else {
            $db = $table;
        }

        // Decide which columns and relations to be queried with
        $this->setupRelations($db, $relations, $relCount, true);
        $this->setupColumns($db, $columns, true);

        $db = $db->where($key, $id);

        if ($this->relations) $db = $db->with($this->relations);
        if ($this->relCount)  $db = $db->withCount($this->relCount);

        if ($this->columns)   $entity = $db->first($this->columns);
        else                  $entity = $db->first();

        return $entity;
    }

    /**
     * Create a new entity
     * @param $inputs - request inputs
     * @return object
     */
    protected function postEntity($inputs)
    {
        // TODO: update column name
        unset($inputs['created_at'], $inputs['updated_at']);

        $table = $this->getEntityTable();

        // Set default entity author/editor to current user if they are not set
        $user_id = $this->guard()->user()->id;
        if ($this->etype == ETYPE_TOPIC)
            if (!isset($inputs['editor_id'])) $inputs['editor_id'] = $user_id;
        if (!isset($inputs['author_id'])) $inputs['author_id'] = $user_id;

        // Normalize HTML and add Angular specific tags
        if (isset($inputs['content']))
            $inputs['content'] = $this->htmlFilter($inputs['content'], $this->www);

        // Create the entity
        $record = $table->create($inputs);

        // Fail to creat the entity
        if (!$record) return null;

        // Update entity relations
        $this->updateRelations($inputs, $record);

        // NOTE: We do not need to create revision when create a new entity

        // Return newly created entity
        return $this->getEntity('id', $record->id, $table);
    }

    /**
     * Update entity by given id
     * @param $inputs - request inputs
     * @param $id - post id to be updated
     * @param $relations - relations to return when 'put' success
     * @param $columns - columns to return when 'put' success
     * @return object
     */
    protected function putEntity($inputs, $key, $id,
                                 $relations = null,
                                 $columns = null,
                                 $relCount = null)
    {
        unset($inputs['created_at'], $inputs['updated_at']);

        $table = $this->getEntityTable();

        $record = $table->where($key, $id)->first();

        $user = $this->guard()->user();

        // Check if user has write permission to the entity
        if (!$this->canUserEditEntity($record, $user))
            return null;

        // Set default entity author/editor to current user if they are not set
        if ($this->etype == ETYPE_TOPIC && !$record->editor_id)
            if (!isset($inputs['editor_id'])) $inputs['editor_id'] = $user->id;
        if (!$record->author_id)
            if (!isset($inputs['author_id'])) $inputs['author_id'] = $user->id;

        // Update entity relations
        $this->updateRelations($inputs, $record);

        // Normalize HTML and add Angular2 specific tags
        if (isset($inputs['content']))
            $inputs['content'] = $this->htmlFilter($inputs['content'], $this->www);

        // Save old post before updating it, only apply to content support
        // revision and are not autosaved.
        if (isset($inputs['content']) && $this->supportRevision() &&
            !isset($inputs['auto'])) {
            $record->revisions()->create([
                'status'       => $record->status,
                'user_id'      => $user->id,
                'body'         => $record->content]);
        }

        // Remove unwanted columns
        unset($inputs['etype'], $inputs['auto']);

        if ($record->update($inputs)) {
            // Return the updated entity
            return $this->getEntity('id', $id, $table, $relations,
                $columns, $relCount);
        }

        // Return null if any error happens
        return null;
    }

    /**
     * Move an entity into trash, or physically delete an entity from trash
     * This function can't be used by Attachment.
     * @param $key   - primary id name
     * @param $id    - primary id
     * @return bool  - true if we've found the record else false
     */
    protected function deleteEntity($key, $id)
    {
        $table = $this->getEntityTable();
        if (!$table) return false;

        $record = $table->where($key, $id)->first();

        if ($record && $record->status == 'trash') {
            // Move entity to trash
            $record->status = 'trash';
            $record->save();
            return true;
        } else if ($record && $record->status == 'trash') {
            // Physically delete an entity from trash
            // TODO: Check if relationship is automatically deleted as we
            // have FK constraint on it.
            $table->where($key, $id)->delete();
            return true;
        }

        return false;
    }

    /**
     * Move multiple entities into trash by their IDs
     * @param $ids
     * @return bool|int
     */
    protected function deleteEntities($ids)
    {
        $numDeleted = 0;

        if (!$ids) return false;

        $idAry = explode(',', $ids);

        foreach($idAry as $id) {
            if ($this->deleteEntity('id', $id))
                $numDeleted++;
        }

        return $numDeleted;
    }

    /**
     * Update entity relations.
     * @param $inputs
     * @param $entity
     */
    protected function updateRelations($inputs, $entity)
    {
        if (isset($inputs['tags'])) {
            $tagIds = array_column($inputs['tags'], 'id');
            $entity->tags()->sync($tagIds);
        }

        if (isset($inputs['categories'])) {
            $catIds = array_column($inputs['categories'], 'id');
            $entity->categories()->sync($catIds);
        }

        // Topic/Product entity may have multiple images
        if (isset($inputs['images'])) {
            $imgIds = array_column($inputs['images'], 'id');
            $entity->images()->sync($imgIds);
        }

        if (isset($inputs['topics'])) {
            $topicIds = array_column($inputs['topics'], 'id');

            if ($this->etype == ETYPE_TOPIC) {
                // Update topic_has_topic relations in 2 directions

                // The relationship
                $entity->topics()->sync($topicIds);
                // The reverse relationship
                $entity->topics_reverse()->sync($topicIds);
            } else {
                // One direction relationship between topic and non topic.
                $entity->topics()->sync($topicIds);
            }
        }

        // TODO: It is not efficient to update all offers of given topic.
        // Client side supports modify multiple offers with a topic entity
        if (isset($inputs['offers'])) {
            // Update the record of each offer
            $table = $this->getEntityTable(ETYPE_OFFER);
            foreach ($inputs['offers'] as $offer) {
                $table->where('id', $offer['id'])->update($offer);
            }
            // Update the pivot table topic_has_offer.
            $offerIds = array_column($inputs['offers'], 'id');
            $entity->offers()->sync($offerIds);
        }

        // Update entity word count and others
        if (isset($inputs['statistic'])) {
            $word_count = $inputs['statistic']['word_count'];
            $entity->statistic()->update(['word_count' => $word_count]);
        }
    }


    /**
     * Check if current user has permission to edit a entity.
     * can be edited by editor and above, post can be edited by author and above
     * @param $record - a cms record
     * @param $user - current user
     * @return bool
     */
    private function canUserEditEntity($record, $user)
    {
        // Author can edit his posts
        if ($this->etype == ETYPE_POST && $user->hasRole('author'))
            return $record->author_id == $user->id ? true: false;

        // Editor and administrator can edit any content
        if ($user->hasRole(['editor', 'administrator']))
            return true;

        return false;
    }

    /**
     * A helper function that return list of entities and it's pagination
     * either simple/full pagination will be returned with
     * @param $db
     * @return array
     */
    private function getEntitiesInternal($db)
    {
        //\Illuminate\Support\Facades\DB::enableQueryLog();

        // Get total count for pagination where every filter is applied
        if ($this->pagination == 'full') $total = $db->count();
        else                             $total = 0;

        // Ordering
        if ($this->orderBy && $this->order)
            $db = $db->orderBy($this->orderBy, $this->order);
        else
            $db = $db->orderBy('updated_at', 'desc');

        $db = $db->skip($this->skipNum)->take($this->perPage);

        // Query with relations
        if ($this->relations)   $db = $db->with($this->relations);

        // withCount overwrites columns in get, must use select before it
        if ($this->columns) $db = $db->select($this->columns);

        // Count related model, say the number of offers associated with a topic.
        if ($this->relCount)   $db = $db->withCount($this->relCount);

        $records = $db->get();

        //var_export(\Illuminate\Support\Facades\DB::getQueryLog());

        return ['total' => $total, 'entities' => $records];
    }

    /**
     * Get list of entities w/wo filters
     * @param $table      - entity table object
     * @param $tableName  - entity table name in string
     * @return array
     */
    private function filterEntities($table, $tableName)
    {
        $db = $table;

        // Query with date from/to
        if (isset($this->date['type']))
            $db = $db->whereBetween($this->date['type'],
                [$this->date['from'], $this->date['to']]);

        // Query with entity status.
        if ($this->status) $db = $db->where('status', $this->status);

        // Query with channel, channel can be ether channel id or slug
        //if ($this->channel)
        //    $db = $this->filterByChannel($db, $tableName, $this->channel);

        //if($this->cType && $this->cId){
        //    $db = $this->filterByCommentAble($db, $tableName, $this->cType, $this->cId);
        //}

        // Advertise position
        if ($this->position)
            $db = $db->where('position', $this->position);

        // Query with category
        if ($this->category)
            $db = $this->filterByCategory($db, $tableName, $this->category);

        // If the entity if featured or not
        if ($this->featured)
            $db = $this->filterByFeatured($db, $tableName, $this->featured);

        // TOPIC ENTITY ONLY: Query with topic type
        if ($this->topicType)
            $db = $this->filterTopicByType($db, $tableName, $this->topicType);

        // TOPIC ENTITY ONLY: Only get topic with at least 1 offer
        if ($this->topicHasOffer)
            $db = $this->filterTopicHasOffer($db, $tableName, $this->topicHasOffer);

        // TOPIC ENTITY ONLY: Only get topic with at least 1 featured offer
        if ($this->topicHasFeaturedOffer)
            $db = $this->filterTopicHasFeaturedOffer($db, $tableName,
                $this->topicHasFeaturedOffer);

        // TOPIC ENTITY ONLY: Only get topic whose guid starts with given characters
        if ($this->topicGuidStarts)
            $db = $this->filterTopicGuidStarts($db, $tableName, $this->topicGuidStarts);

        // Query with topic which entity belongs to
        if ($this->topic)
            $db = $this->filterByTopic($db, $tableName, $this->topic);

        // Query with author
        if ($this->author)
            $db = $this->filterByAuthor($db, $tableName, $this->author);

        // Query with editor
        if ($this->editor)
            $db = $this->filterByEditor($db, $tableName, $this->editor);

        // Search entities by given keyword
        if ($this->query)
            $db = $this->filterBySearchString($db, $this->etype, $this->query);

        return $db;
    }

    /**
     * Setup entity list filters from incoming request parameters
     * @param $inputs - array of request parameters
     */
    private function initInputFilters($inputs)
    {
        /* Number of posts per page, default 20 */
        $this->perPage = isset($inputs['per_page']) ? intval($inputs['per_page']) : 20;
        /* Current page index, default 1 */
        $this->curPage = isset($inputs['page']) ? intval($inputs['page']) : 1;

        /* Filter: date filter, must be given as trinity */
        $this->date = [];
        /* Date type: published_at, created_at or updated_at etc */
        $datetype = isset($inputs['datetype']) ? $inputs['datetype'] : null;
        $datefrom = isset($inputs['datefrom']) ? $inputs['datefrom'] : null;
        $dateto   = isset($inputs['dateto']) ? $inputs['dateto'] : null;
        if ($datetype) {
            array_push($this->date,
                ['type' => $datetype, 'datefrom' => $datefrom, 'dateto' => $dateto]);
        }

        /* Filter: entity channel */
        $this->channel = isset($inputs['channel']) ? $inputs['channel'] : null;
        /* Filter: entity channel */
        $this->cType = isset($inputs['cType']) ? $inputs['cType'] : null;
        /* Filter: entity channel */
        $this->cId = isset($inputs['cId']) ? $inputs['cId'] : null;

        /* Filter: advertise position */
        $this->position = isset($inputs['position']) ? $inputs['position'] : null;

        /* Filter: entity has given category */
        $this->category = isset($inputs['category']) ? $inputs['category'] : null;

        /* Filter: entity is marked as featured or not */
        $this->featured = isset($inputs['featured']) ? $inputs['featured'] : null;

        /* Filter: entity(post/offer) belongs to given topic */
        $this->topic = isset($inputs['topic']) ? $inputs['topic'] : null;

        /* Filter: entity status, if 'all' is assigned to 'status', it equals
         * to query with any statuss. */
        $this->status = isset($inputs['status']) ?
            ($inputs['status'] == 'all' ? null : $inputs['status']) : null;

        /* Filter: entity author if applicable */
        $this->author = isset($inputs['author']) ? $inputs['author'] : null;

        /* Filter: entity editor if applicable */
        $this->editor = isset($inputs['editor']) ? $inputs['editor'] : null;

        /* Filter: search query string */
        $this->query = isset($inputs['query']) ? $inputs['query'] : null;

        /*********************************************************************/
        /* Topic related filters                                             */
        /* Topic type */
        $this->topicType = isset($inputs['type']) ? $inputs['type'] : null;
        if (!$this->topicType)
            $this->topicType = isset($inputs['topic_type']) ? $inputs['topic_type'] : null;

        /* Topic has offers associated */
        $this->topicHasOffer =
            isset($inputs['topic_has_offer']) ? $inputs['topic_has_offer'] : null;

        /* Topic has featured offers associated */
        $this->topicHasFeaturedOffer = isset($inputs['topic_has_featured_offer']) ?
            $inputs['topic_has_featured_offer'] : null;

        /* Topic guid starts with given character[s] */
        $this->topicGuidStarts =
            isset($inputs['topic_guid_starts']) ? $inputs['topic_guid_starts'] : null;

        /* Ordering */
        $this->orderBy = isset($inputs['order_by']) ? $inputs['order_by'] : null;
        $this->order    = isset($inputs['order']) ? $inputs['order'] : null;

        /* Number of skipped records for current page */
        $this->skipNum = ($this->curPage - 1) * $this->perPage;
    }

    /**
     * Determine what relations of the entity to query together
     *
     * @param $relations- User input relations if any
     * @param $full     - If full relations should be queried
     */
    private function setupRelations($table, $relations,
                                    $relCount, $full)
    {
        if ($relations == null) {
            if ($full) $this->relations = $table->fullRelations();
            else       $this->relations = $table->simpleRelations();
        } else {
            $this->relations = $relations;
        }

        if ($relCount) $this->relCount = $relCount;
    }

    /**
     * Determine what columns of the entity to query together
     *
     * @param $columns  - User specified columns if any
     * @param $full     - If full columns should be queried
     */
    private function setupColumns($table, $columns, $full)
    {
        if ($columns == null) {
            if ($full) $this->columns = $table->fullColumns();
            else       $this->columns = $table->simpleColumns();
        } else {
            $this->columns = $columns;
        }
    }

    /**
     * OVERLOADED BY CHILD CLASS
     * Get entity table to be queried, reference bangli-admin-spa
     * models/entity.ts ENTITY constant for all possible entity type
     * Largely use database view for frontend request to simplify the code
     * base and boost performance.
     *
     * @return object       - entity table
     */
    protected function getEntityTable()
    {
        switch ($this->etype) {
            case ETYPE_POST:         return new Post;
            case ETYPE_OFFER:        return new Offer;
            case ETYPE_PAGE:         return new Page;
            case ETYPE_TOPIC:        return new Topic;
            case ETYPE_ADVERTISE:    return new Advertise;
            case ETYPE_NEWSLETTER:   return new Newsletter;
            case ETYPE_ATTACHMENT:   return new Attachment;
            case ETYPE_COMMENT:      return new Comment;
            default:                 return null;
        }
    }

    /**
     * OVERLOADED BY CHILD CLASS
     * This function return the table name in string, it is used in sql join
     * @return string - table name
     */
    protected function getTableName()
    {

        switch ($this->etype) {
            case ETYPE_POST:          return 'posts';
            case ETYPE_OFFER:         return 'offers';
            case ETYPE_PAGE:          return 'pages';
            case ETYPE_TOPIC:         return 'topics';
            case ETYPE_ADVERTISE:     return 'advertises';
            case ETYPE_NEWSLETTER:    return 'newsletters';
            case ETYPE_ATTACHMENT:    return 'attachments';
            case ETYPE_COMMENT:       return 'comments';
            default:                  return null;
        }
    }

    /**
     * If given entity type support revisions
     * @return bool
     */
    protected function supportRevision()
    {
        switch ($this->etype) {
            case ETYPE_POST:
            case ETYPE_TOPIC:
            case ETYPE_PAGE:
            case ETYPE_NEWSLETTER:
                return true;
            default:
                return false;
        }
    }
}
