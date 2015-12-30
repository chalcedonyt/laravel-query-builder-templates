<?php

namespace DummyScopes;

use Chalcedonyt\QueryBuilderTemplate\Scopes\AbstractScope;

class PostHavingMinimumViewScope extends AbstractScope
{
    /**
     * @var  Array tables that are used by this query.
     */
    protected $tables = ['test_users', 'test_posts'];

    /**
    * @var
    */
    public $views;

    /**
    *
    *  @param   $views
    *
    */
    public function __construct($views)
    {
        $this -> views = $views;
    }

    /**
     * Customize this to define how queries should be set up.
     */
    public function setup()
    {
        //DB::statement('CREATE TEMPORARY TABLE ...');
    }

    /**
     * Modify a Builder object. Changes here can be nested
     * @param  \Illuminate\Database\Query\Builder $query
     * @return  \Illuminate\Database\Query\Builder $query
     */
    public function apply( \Illuminate\Database\Query\Builder $query )
    {        
        return $query -> groupBy('test_users.id')
                      -> having('test_posts.views', '>=', $this -> views);
    }

}
?>
