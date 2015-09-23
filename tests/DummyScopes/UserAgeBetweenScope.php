<?php

namespace DummyScopes;

use Chalcedonyt\QueryBuilderTemplate\Scopes\AbstractScope;
use Carbon\Carbon;

class UserAgeBetweenScope extends AbstractScope
{
    /**
     * @var  Array tables that are used by this query.
     */
    protected $tables = ['test_users'];

    /**
    * @var
    */
    public $minAge;

    /**
    * @var
    */
    public $maxAge;

    /**
    *
    *  @param   $ageMin
    *  @param   $ageMax
    *
    */
    public function __construct($min_age, $max_age)
    {
        $this -> minAge = $min_age;
        $this -> maxAge = $max_age;
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
        //modify $query here
        if( $this -> minAge )
            $query -> where('test_users.dob','<=', Carbon::now() -> subYears($this -> minAge) -> toDateTimeString() );
        if( $this -> maxAge )
            $query -> where('test_users.dob','>=', Carbon::now() -> subYears($this -> maxAge) -> toDateTimeString() );
        return $query;
    }

}
?>
