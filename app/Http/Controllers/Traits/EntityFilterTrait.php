<?php
/**
 * List of traits that used to filter entities by different conditions
 */

namespace App\Http\Controllers\Traits;

trait EntityFilterTrait
{
    /**
     * Filter entity by author ID or name
     *
     * @param $table       - Database table object
     * @param $tableName   - Database table name
     * @param $author      - author ID(number) or name(string)
     * @return mixed       - Filtered database table object
     */
    public function filterByAuthor($table, $tableName, $author)
    {
        if (is_numeric($author)) // Query by author ID
            return $table->where('author_id', $author);
        else                     // Query by author name
            return $table->join('users',
                function($join) use ($tableName, $author) {
                    $join->on($tableName. '.author_id', '=', 'users.id')
                        ->where('users.name', '=', $author);
                });
    }

    /**
     * Filter entity by editor ID or name
     *
     * @param $table       - Database table object
     * @param $tableName   - Database table name
     * @param $editor      - editor ID(number) or name(string)
     * @return mixed       - Filtered database table object
     */
    public function filterByEditor($table, $tableName, $editor)
    {
        if (is_numeric($editor)) // Query by author ID
            return $table->where('editor_id', $editor);
        else                     // Query by author name
            return $table->join('users',
                function($join) use ($tableName, $editor) {
                    $join->on($tableName. '.editor_id', '=', 'users.id')
                        ->where('users.name', '=', $editor);
                });
    }

    /**
     * Filter entity by channel ID or slug
     *
     * @param $table
     * @param $tableName
     * @param $channel
     * @return mixed
     */
    public function filterByChannel($table, $tableName, $channel)
    {
        if (is_numeric($channel))  // Query by channel ID
            return $table->where('channel_id', $channel);
        else
            return $table->join('channels',
                function($join) use ($tableName, $channel) {
                    $join->on($tableName . '.channel_id', '=', 'channels.id')
                        ->where('channels.slug', '=', $channel);
                });
    }

    /**
     * Filter entity by category ID or slug
     *
     * @param $table
     * @param $tableName
     * @param $category
     * @return mixed
     */
    public function filterByCategory($table, $tableName, $category)
    {
        return $table->whereHas('categories', function ($q) use ($category) {
            if (is_numeric($category)) // Query by category ID
                $q->where('categories.id', '=', $category);
            else
                $q->where('categories.slug', '=', $category);
        });
    }

    /**
     * Filter entity by topic ID or slug it belongs
     * @param $table
     * @param $tableName
     * @param $topic
     */
    public function filterByTopic($table, $tableName, $topic)
    {
        return $table->whereHas('topics', function ($q) use($topic) {
            if (is_numeric($topic)) // Query with topic ID
                $q->where('topics.id', '=', $topic);
            else
                $q->where('topics.guid', '=', $topic);
        });
    }

    /**
     * Filter entity(ETYPE_TOPIC) by topic type ID or slug
     *
     * @param $table
     * @param $tableName
     * @param $type
     * @return mixed
     */
    public function filterTopicByType($table, $tableName, $type)
    {
       if (is_numeric($type))  // Query by topic type ID
           return $table->where('type_id', $type);
       else
           return $table->join('topic_types',
               function($join) use ($tableName, $type) {
                  $join->on($tableName . '.type_id', '=', 'topic_types.id')
                      ->where('topic_types.slug', '=', $type);
               });
    }

    /**
     * Filter entity(TOPIC) if it has offer associated.
     * @param $table
     * @param $tableName
     * @return mixed
     */
    public function filterTopicHasOffer($table, $tableName, $hasOffer)
    {
        if ($hasOffer)
            return $table->has('offers');
        else
            return $table->doesntHave('offers');
    }

    public function filterTopicGuidStarts($table, $tableName, $guidStarts)
    {
        return $table->where('guid', 'like', $guidStarts . '%');
    }

    /**
     * Simple title search of entity
     *
     * @param $table
     * @param $etype
     * @param $text
     * @return mixed
     */
    public function filterBySearchString($table, $etype, $text)
    {
        $text = '%' . $text . '%';
        return $table->where(function ($query) use ($etype, $text) {
            if ($etype == ETYPE_TOPIC)  // Search in topic
                $query->where('title', 'like', $text)
                    ->orWhere('guid', 'like', $text);
            else if ($etype == ETYPE_ATTACHMENT) // Search in attachment
                $query->where('title', 'like', $text)
                    ->orWhere('desc', 'like', $text)
                    ->orWhere('filename', 'like', $text);
            else // Search in any other entities
                $query->where('title', 'like', $text);
        });
    }
}