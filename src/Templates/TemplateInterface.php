<?php
namespace Chalcedonyt\QueryBuilderTemplate\Templates;

use Chalcedonyt\QueryBuilderTemplate\Scopes\ScopeInterface;

interface TemplateInterface
{

    /**
     * @param \Illuminate\Database\Query\Builder $baseQuery
     * Constructor should receive a base query, e.g. one that has a base DB::table and DB::select call.
     */
    public function __construct(\Illuminate\Database\Query\Builder $baseQuery);

    /**
     * Adds a scope to the buffer
     * @param ScopeInterface $scope
     * @param String exists|or|and
     * @return TemplateInterface $this
     */
    public function add( ScopeInterface $scope, $method = '' );

    /**
     * Adds a scope to the buffer, such as "having", "group by", or any other blocks that couldn't be fulfilled by addRequired() and addOptional()
     * @param ScopeInterface $scope
     * @return TemplateInterface $this
     */
    public function addDirect(ScopeInterface $scope);

    /**
     * Adds a scope to the buffer, to be wrapped in an orWhere block
     * @param ScopeInterface $scope
     * @return TemplateInterface $this
     */
    public function addOptional(ScopeInterface $scope);

    /**
     * Adds a scope to the buffer, wrapped in a Where block
     * @param ScopeInterface $scope
     * @return TemplateInterface $this
     */
    public function addRequired(ScopeInterface $scope);

    /**
     * Resolves all the QueryBuilderSpecificationInterface items into the current Builder.
     * @return \Illuminate\Database\Query\Builder $query
     */
    public function generate();

    /**
     * Resets all scopes
     * @return \Illuminate\Database\Query\Builder $query
     */
    public function clear();

}
?>
