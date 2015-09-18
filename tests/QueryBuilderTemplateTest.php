<?php
/**
 * Tests all exposed index and show endpoints
 */
use DummyScopes\UserAgeBetweenScope;
use DummyScopes\UserGenderScope;
use DummyScopes\UserGroupScope;
use DummyScopes\UserPostCountBetweenScope;
use DummyScopes\PostIsMaxDaysOldScope;
use DummyScopes\InvalidScopeTest;

use DummyTemplates\PostTemplateFactory;
use Illuminate\FileSystem\Filesystem;
use Illuminate\FileSystem\ClassFinder;

class QueryBuilderTemplateTest extends Orchestra\Testbench\TestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->app['config']->set('database.default','sqlite');
        $this->app['config']->set('database.connections.sqlite.database', ':memory:');

       $this->migrate();
    }

    /**
     * run package database migrations
     *
     * @return void
     */
    public function migrate()
    {
        $fileSystem = new Filesystem;
        $classFinder = new ClassFinder;

        foreach($fileSystem->files(__DIR__ . "/migrations") as $file)
        {
            $fileSystem->requireOnce($file);
            $migrationClass = $classFinder->findClass($file);

            (new $migrationClass)->up();
        }
        foreach($fileSystem->files(__DIR__ . "/seeds") as $file)
        {
            $fileSystem->requireOnce($file);
            $migrationClass = $classFinder->findClass($file);

            (new $migrationClass)->run();
        }
    }

    public function testRequired()
    {
        $factory = new PostTemplateFactory();
        $template = $factory -> create();
        //all
        $this -> assertEquals( $this -> numberOfResults( $template ), 40 );

        //10 young users*2
        $young_scope = new UserAgeBetweenScope(20, 30);
        $template -> addRequired($young_scope);
        $this -> assertEquals( $this -> numberOfResults( $template ), 20 );

        //5 male young users*2
        $male_scope = new UserGenderScope(1);
        $template -> addRequired($male_scope);
        $this -> assertEquals( $this -> numberOfResults( $template ), 10 );

        $post_age_scope = new PostIsMaxDaysOldScope(7);
        $template -> addRequired($post_age_scope);
        $this -> assertEquals( $this -> numberOfResults( $template ), 5 );

    }

    //when a scope is added with a Join request that the template cannot handle
    public function testJoinInvalidScope()
    {
        $factory = new PostTemplateFactory();
        $invalid_scope = new InvalidScopeTest();
        $template = $factory -> create();
        $template -> addRequired($invalid_scope);

        $this -> setExpectedException('Chalcedonyt\QueryBuilderTemplate\Exception\QueryBuilderTemplateException');
        $template -> generate();
    }

    private function numberOfResults( $template )
    {
        return count($template -> generate() -> get() );
    }



}

?>
