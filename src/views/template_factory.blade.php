<?= '<?php' ?>


namespace {{$namespace}};

use Chalcedonyt\QueryBuilderTemplate\Templates\Factory\AbstractTemplateFactory;

class {{$classname}} extends AbstractTemplateFactory
{
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
    * Set properties here for a parameterized factory.
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
     * The base query. DB::table and ::select should be set here
     * @return Builder
     */
    protected function getBaseQuery()
    {
        //return DB::table('users') -> select('users.*');
    }

    /**
     * Possible Joins to make depending on the ScopeInterface objects added.
     * @return Array of Illuminate\Database\Query\JoinClause
     */
    protected function getAvailableJoinClauses(){
        /*join with the survey table
        $post_join = new JoinClause('inner','posts');
        $post_join -> on('post.user_id','=','users.id');

        return [
            $post_join
        ];*/

        return [];
    }
}
?>
