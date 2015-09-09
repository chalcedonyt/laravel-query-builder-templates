<?php

namespace App\Providers;

use Chalcedonyt\QueryBuilderTemplate\Providers\QueryBuilderTemplateServiceProvider as ServiceProvider;

class QueryBuilderTemplateServiceProvider extends ServiceProvider
{
    /**
     * The resolvers
     *
     * @var array
     */
    protected $templates = [
        //'DummyTemplate' => \App\QueryBuilderTemplate\Templates\Factory\DummyTemplate::class,
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
    }
}
?>
