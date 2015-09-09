<?php

namespace DummyScopes;

use Chalcedonyt\QueryBuilderTemplate\Scopes\AbstractScope;

class InvalidScopeTest extends AbstractScope
{
    /**
     * @var  Array tables that are used by this query.
     */
    protected $tables = ['invalid_table'];



    /**
     * Modify a Builder object. Changes here can be nested
     * @param  \Illuminate\Database\Query\Builder $query
     * @return  \Illuminate\Database\Query\Builder $query
     */
    public function apply( \Illuminate\Database\Query\Builder $query )
    {
        return $query;
    }

}
?>
