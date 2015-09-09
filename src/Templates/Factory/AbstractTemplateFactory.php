<?php
namespace Chalcedonyt\QueryBuilderTemplate\Templates\Factory;
use Chalcedonyt\QueryBuilderTemplate\Templates\Template;
use Illuminate\Database\Query\JoinClause;
use DB;

abstract class AbstractTemplateFactory implements TemplateFactoryInterface
{
    /**
     * Sets up the QuerySpecificationsTemplate and returns it
     * @return QuerySpecificationsTemplateInterface $template
     */
    public function create(){
        $query = $this -> getBaseQuery();

        $template = new Template( $query );
        $template -> setAvailableJoinClauses( $this -> getAvailableJoinClauses() );
        return $template;
    }

    /**
     * The base query. DB::table and ::select should be set here
     * @return Builder
     */
    abstract protected function getBaseQuery();

    /**
     * Possible Joins to make depending on specifications passed.
     * @return Array of Illuminate\Database\Query\JoinClause
     */
    abstract protected function getAvailableJoinClauses();
}
?>
