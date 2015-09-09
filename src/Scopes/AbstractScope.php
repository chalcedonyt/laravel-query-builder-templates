<?php
namespace Chalcedonyt\QueryBuilderTemplate\Scopes;

abstract class AbstractScope implements ScopeInterface
{
    /**
     * @var Array tables that are needed by this scope.
     */
    protected $tables = [];

    /**
     * Apply the changes to a Builder object
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder $query
     */
    abstract public function apply( \Illuminate\Database\Query\Builder $query );

    /**
     * Call apply() within an "or" block
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder $query
     */
    public function applyOr(\Illuminate\Database\Query\Builder $query )
    {
        $query -> orWhere( function($query){
            $this -> apply($query);
        });
        return $query;
    }

    /**
     * Call apply() within an "and" block
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder $query
     */
    public function applyAnd(\Illuminate\Database\Query\Builder $query )
    {
        $query -> where( function($query){
            $this -> apply($query);
        });
        return $query;
    }

    /**
     * To be run before the scope's template is generated, e.g. temporary tables or caching
     * @return void
     */
    public function setup()
    {

    }

    /**
     * @return Array of tables used
     */
    public function getTables()
    {
        return $this -> tables;
    }
}
?>
