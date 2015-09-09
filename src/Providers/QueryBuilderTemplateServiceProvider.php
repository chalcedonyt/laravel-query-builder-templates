<?php

namespace Chalcedonyt\QueryBuilderTemplate\Providers;
use Chalcedonyt\QueryBuilderTemplate\Commands\ScopeGeneratorCommand;
use Chalcedonyt\QueryBuilderTemplate\Commands\TemplateFactoryGeneratorCommand;
use Illuminate\Support\ServiceProvider;

class QueryBuilderTemplateServiceProvider extends ServiceProvider
{
    protected $templates = [];

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $source_config = __DIR__ . '/../config/query_builder_template.php';
        $this->publishes([
            $source_config => base_path('config/query_builder_template.php'),
            __DIR__.'/../views/provider.php' => base_path('app/Providers/QueryBuilderTemplateServiceProvider.php')
        ]);

        $this->loadViewsFrom(__DIR__ . '/../views', 'query_builder_template');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $source_config = __DIR__ . '/../config/query_builder_template.php';
        $this->mergeConfigFrom($source_config, 'query_builder_template');

        //commands to generate a scope
        $this->app['command.qbt_scope.generate'] = $this->app->share(
            function ($app) {
                return new ScopeGeneratorCommand($app['config'], $app['view'], $app['files']);
            }
        );
        $this->commands('command.qbt_scope.generate');

        //commands to generate a new template
        $this->app['command.qbt_template_factory.generate'] = $this->app->share(
            function ($app) {
                return new TemplateFactoryGeneratorCommand($app['config'], $app['view'], $app['files']);
            }
        );
        $this->commands('command.qbt_template_factory.generate');
        $this -> registerTemplates();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['command.qbt_template_factory.generate','command.qbt_scope.generate'];
    }

    private function registerTemplates()
    {
        foreach( $this -> templates as $key => $class)
        {
            $this -> app -> bind( $key, function($app, $args) use( $key, $class){
                $reflect = new \ReflectionClass($class);
                $factory = $reflect -> newInstanceArgs( $args );
                $resolver = $factory -> create();
                return $resolver;
            });
        }
    }
}
