<?php

namespace DummyTemplates;

use Chalcedonyt\QueryBuilderTemplate\Templates\Factory\AbstractTemplateFactory;

class UserGroupReportTemplateFactory extends AbstractTemplateFactory
{
    /**
     * The base query. DB::table and ::select should be set here
     * @return  Builder
     */
    protected function getBaseQuery()
    {
        return DB::table('test_user_groups') -> select('test_user_groups.*');
    }

    /**
     * Possible Joins to make depending on the ScopeInterface objects added.
     * @return  Array of Illuminate\Database\Query\JoinClause
     */
    protected function getAvailableJoinClauses(){

        return [
        ];
    }
}
?>
