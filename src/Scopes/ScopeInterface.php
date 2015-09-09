<?php
namespace Chalcedonyt\QueryBuilderTemplate\Scopes;

interface ScopeInterface
{
    /**
     * Apply the changes to a Builder object
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder $query
     */
    public function apply( \Illuminate\Database\Query\Builder $query );
    /**
     * Call apply() within an "or" block
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder $query
     */
    public function applyOr(\Illuminate\Database\Query\Builder $query );

    /**
     * Call apply() within an "and" block
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder $query
     */
    public function applyAnd(\Illuminate\Database\Query\Builder $query );

    /**
     * To be run before the scope's template is generated, e.g. temporary tables or caching
     * @return void
     */
    public function setup();

    /**
     * @return Array of tables used
     */
    public function getTables();

}
?>
