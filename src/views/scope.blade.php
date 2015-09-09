<?= '<?php' ?>


namespace {{$namespace}};

use Chalcedonyt\QueryBuilderTemplate\Scopes\AbstractScope;

class {{$classname}} extends AbstractScope
{
    /**
     * @var Array tables that are used by this query.
     */
    protected $tables = [];

@if (count($parameters))
@foreach( $parameters as $param)
    /**
    * @var {{$param['class']}}
    */
    protected ${{camel_case($param['name'])}};

@endforeach
@endif
    /**
    *
@if (!count($parameters))
    * Set properties here for a parameterized scope.
@else
@foreach( $parameters as $param)
    *  @param {{$param['class']}} ${{camel_case($param['name'])}}
@endforeach
@endif
    *
    */
    public function __construct({{$parameter_string}})
    {
@if (count($parameters))
@foreach( $parameters as $param)
        $this -> {{camel_case($param['name'])}} = ${{$param['name']}};
@endforeach
@endif
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
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder $query
     */
    public function apply( \Illuminate\Database\Query\Builder $query )
    {
        //modify $query here
        return $query;
    }

}
?>
