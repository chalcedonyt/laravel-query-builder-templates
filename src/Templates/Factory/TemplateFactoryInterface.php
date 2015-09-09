<?php
namespace Chalcedonyt\QueryBuilderTemplate\Templates\Factory;

interface TemplateFactoryInterface
{
    /**
     * Sets up the QuerySpecificationsTemplate and returns it
     * @return QuerySpecificationsTemplateInterface $template
     */
    public function create();

}
?>
