<?php

return [
    /*
      |----------------------------------------------------------------------------------------------------------
      | namespace
      |----------------------------------------------------------------------------------------------------------
     */
    'namespace'  => env('QUERY_BUILDER_TEMPLATE_NAMESPACE', 'App\QueryBuilderTemplate'),
    /*
      |----------------------------------------------------------------------------------------------------------
      | Transformers store directory
      | path relative to App/
      |----------------------------------------------------------------------------------------------------------
     */

    'directory'  => env('QUERY_BUILDER_TEMPLATE_DIRECTORY', 'QueryBuilderTemplate/')

];
