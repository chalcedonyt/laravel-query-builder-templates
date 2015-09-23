<?php

namespace DummyScopes;

use Chalcedonyt\QueryBuilderTemplate\Scopes\AbstractScope;

class PostIsMaxDaysOldScope extends AbstractScope
{
    /**
     * @var  Array tables that are used by this query.
     */
    protected $tables = ['test_posts'];

    /**
    * @var
    */
    public $daysOld;

    /**
    *
    *  @param   $daysOld
    *
    */
    public function __construct($days_old)
    {
        $this -> daysOld = $days_old;
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
        $date =date('Y-m-d H:i:s', strtotime('-'.$this -> daysOld.' day'));
        return $query -> where('test_posts.created_at','<=', $date);
    }

}
?>
