<?php
namespace DummyTemplates;
use \Illuminate\Database\Query\JoinClause;
use \DB;

class PostTemplateFactory extends \Chalcedonyt\QueryBuilderTemplate\Templates\Factory\AbstractTemplateFactory
{
    /**
     * The base query. DB::table and ::select should be set here
     * @return Builder
     */
    protected function getBaseQuery()
    {
        return DB::table('test_posts')
        -> select('test_posts.*');
    }

    /**
     * Possible Joins to make depending on specifications passed.
     * @return Array of Illuminate\Database\Query\JoinClause
     */
    protected function getAvailableJoinClauses(){
        //join with the test_users table
        $user_join = new JoinClause('inner','test_users');
        $user_join -> on('test_users.id','=','test_posts.user_id');

        return [
            $user_join
        ];
    }
}
?>
