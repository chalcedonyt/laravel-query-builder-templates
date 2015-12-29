<?php
namespace Chalcedonyt\QueryBuilderTemplate\Templates;

use Chalcedonyt\QueryBuilderTemplate\Scopes\ScopeInterface;
use Chalcedonyt\QueryBuilderTemplate\Exception\QueryBuilderTemplateException;

class Template implements TemplateInterface
{
    const MODIFIER_DIRECT = 2;
    const MODIFIER_REQUIRED = 1;
    const MODIFIER_OPTIONAL = 0;

    /**
     * @var Collection ScopeInterface
     * ScopeInterface objects that will be applied directly on the root query.
     */
    private $directScopes;

    /**
     * @var Collection ScopeInterface
     * ScopeInterface objects that will be chained using "AND"
     */
    private $requiredScopes;

    /**
     * @var Collection ScopeInterface
     * ScopeInterface objects that will be chained using "OR" inside a single "AND"
     */
    private $optionalScopes;

    /**
     * @var Array availableJoinClauses
     * Possible Illuminate\Database\Query\JoinClause objects to use, if the table is found in a ScopeInterface.
     */
    protected $availableJoinClauses;

    /**
     * @var Array Closure
     * An array of queries that need to be run before returning the Builder
     */
     protected $preExecutionClosures = [];

    /**
     * @var Builder $query
     */
    private $query;

    /**
     * @var Builder $query The base64_encode query
     */
    private $baseQuery;

    /**
     * @param \Illuminate\Database\Query\Builder $baseQuery
     * Constructor should receive a base query,
     * e.g. one that defines the base table, and the columns and groupings using DB::table, DB::select calls.
     */
    public function __construct(\Illuminate\Database\Query\Builder $base_query = null)
    {
        $this -> baseQuery = $base_query;
        $this -> query = $base_query;
        $this -> directScopes = collect([]);
        $this -> requiredScopes = collect([]);
        $this -> optionalScopes = collect([]);
    }

    /**
     * Adds a scope to the buffer
     * @param ScopeInterface $scope
     * @param String exists|or|and
     * @return TemplateInterface $this
     */
    public function add( ScopeInterface $scope, $add_mode = self::MODIFIER_DIRECT )
    {
        switch( $add_mode ){
            case self::MODIFIER_DIRECT:
                $this -> directScopes -> push($scope);
                break;
            case self::MODIFIER_REQUIRED:
                $this -> requiredScopes -> push($scope);
                break;
            case self::MODIFIER_OPTIONAL:
                $this -> optionalScopes -> push($scope);
                break;
            default: break;
        }
        return $this;
    }

    /**
     * Adds a scope to the buffer, wrapped in an orWhere block
     * @param ScopeInterface $scope
     * @return TemplateInterface $this
     */
    public function addOptional(ScopeInterface $scope)
    {
        return $this -> add( $scope, self::MODIFIER_OPTIONAL);
    }

    /**
     * Adds a scope to the buffer, wrapped in a Where block
     * @param ScopeInterface $scope
     * @return TemplateInterface $this
     */
    public function addRequired(ScopeInterface $scope)
    {
        return $this -> add( $scope, self::MODIFIER_REQUIRED);
    }
    
    /**
     * Alias for getBuilder
     */
    public function generate(){
        return $this -> getBuilder();
    }
    /**
     * Resolves all the ScopeInterface items into the current Builder.
     * @return \Illuminate\Database\Query\Builder $query
     */
    public function getBuilder(){
        $this -> query = clone $this -> baseQuery;
        //apply all direct scopes to the query.
        $this -> directScopes -> each( function( $scope ){
            $scope_method = 'apply';
            $this -> query =  $scope -> $scope_method($this -> query);
        });

        //chain all required scopes in "AND" blocks
        $this -> requiredScopes -> each( function( $scope ){
            $scope_method = 'applyAnd';
            $this -> query =  $scope -> $scope_method($this -> query);
        });

        //chain all optional scopes using "OR", nested within a single "AND" block.
        if( $this -> optionalScopes -> count() )
        {
            $this -> query -> where( function($query){
                $this -> optionalScopes -> each( function( $scope ) use ($query){
                    $scope_method = 'applyOr';
                    return $scope -> $scope_method($query);
                });
            });
        }
        collect([]) -> merge( $this -> directScopes )
        -> merge( $this -> requiredScopes)
        -> merge( $this -> optionalScopes )
        -> each( function( $scope ){
            $this -> parseScope( $scope );
        });

        return $this -> query;
    }

    /**
     * Resets everything
     * @return \Illuminate\Database\Query\Builder $query
     */
    public function clear()
    {
        self::__construct($this -> baseQuery);
        return $this -> query;
    }

    /**
     * @param Array Illuminate\Database\Query\JoinClause $clauses
     */
    public function setAvailableJoinClauses( $clauses )
    {
        $this -> availableJoinClauses = $clauses;
    }

    /**
     * Any other methods should be passed into the $query property, after resolving.
     */
    public function __call($method, $arguments)
    {
        $this -> query = $this -> generate();
        return call_user_func_array( [$this -> query, $method], $arguments);
    }

    /**
     * Validates that any required JoinClauses are present, and executes any pre-execution statements
     * @param ScopeInterface
     */
    private function parseScope( ScopeInterface $scope )
    {
        $this -> resolveJoins( $scope -> getTables() );
        $scope -> setup();
    }

    /**
     * Checks the tables to create new joins, or ignore if the tables are already joined.
     * If a table needs to be joined twice, it should be using an alias.
     * @todo This really only works for inner joins now.
     * @param Array of tables
     *
     */
    private function resolveJoins($tables)
    {
        foreach($tables as $table)
        {   //no join is needed if the base table is the same.
            if( $this -> query -> from == $table )
                continue;

            //if an inner join to this table already exists in the query being built, ignore it.
            if( count($this -> getJoinsOnTable( $this -> query -> joins, $table) ) ){
                continue;
            }

            //lookup the JoinClause to use for this table.
            $join_clauses = $this -> getJoinsOnTable( $this -> availableJoinClauses, $table);
            if( !count( $join_clauses ) )
                throw new QueryBuilderTemplateException(__CLASS__.": Tried to join to ".$table." but a rule was not specified");

            if( count( $join_clauses ) > 1 )
                throw new QueryBuilderTemplateException(__CLASS__.": Had more than one clause specified for ".$table);
            $this -> query -> joins[]= $join_clauses[0];
        }
    }

    /**
     * Convenience function to find an inner join on $table inside an array of JoinClauses.
     * @param Array of Illuminate\Database\Query\JoinClause
     * @return Array of Illuminate\Database\Query\JoinClause
     */
    private function getJoinsOnTable( $joins, $table )
    {
        $join_collection = collect($joins);
        return $join_collection
        -> whereLoose('table', $table )
        //reset the keys to integers
        -> values();
    }

}
?>
