<?php

namespace DummyScopes;

use Chalcedonyt\QueryBuilderTemplate\Scopes\AbstractScope;

class UserPostCountBetweenScope extends AbstractScope
{
    /**
     * @var  Array tables that are used by this query.
     */
    protected $tables = ['test_users','test_posts'];

    /**
    * @var
    */
    public $countMin;

    /**
    * @var
    */
    public $countMax;

    /**
    *
    *  @param   $countMin
    *  @param   $countMax
    *
    */
    public function __construct($count_min, $count_max)
    {
        $this -> countMin = $count_min;
        $this -> countMax = $count_max;
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
        $query -> havingRaw('(post_count > '.(int)$this -> countMin.' AND post_count < '.(int)$this -> countMax.')');
        return $query;
    }

}
?>
