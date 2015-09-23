<?php

namespace DummyScopes;

use Chalcedonyt\QueryBuilderTemplate\Scopes\AbstractScope;

class UserGroupHasUserCountScope extends AbstractScope
{
    /**
     * @var  Array tables that are used by this query.
     */
    protected $tables = ['test_user_groups'];

    /**
    * @var
    */
    public $minUserCount;

    /**
    *
    *  @param   $groupId
    *
    */
    public function __construct($min_user_count)
    {
        $this -> minUserCount = $min_user_count;
    }

    /**
     * Customize this to define how queries should be set up.
     */
    public function setup()
    {
        DB::statement("CREATE TEMPORARY TABLE tmp_group_user_count
            SELECT count(test_users.id) as user_count, group_id
            FROM test_user_groups
            GROUP BY user_id");
    }

    /**
     * Modify a Builder object. Changes here can be nested
     * @param  \Illuminate\Database\Query\Builder $query
     * @return  \Illuminate\Database\Query\Builder $query
     */
    public function apply( \Illuminate\Database\Query\Builder $query )
    {
        $group_results = DB::table('tmp_group_user_count') -> select('group_id');
        $query -> whereIn('test_user_groups.group_id', $group_results -> all() );
        return $query;
    }

}
?>
