# laravel-query-builder-templates

Reusable templates and scopes to build complex queries that span multiple tables.

Generally, Eloquent/Specification patterns/Repositories/ should be used on smaller datasets or when only querying on a few tables. This package aims to help reusability when we need to fall back on the query builder.

## Install

Installation is only via Git for now as this isn't ready for Packagist yet. Edit your `composer.json`, adding this under `require`:
```
"chalcedonyt/laravel-query-builder-templates": "~0.*"
```

Create a `repositories` entry if it doesn't exist to tell Composer to load the library from Git:
```
"repositories": [
	{
		"packagist": false,
		"type": "git",
		"url": "https://github.com/chalcedonyt/laravel-query-builder-templates.git"
    }]
```


Then run `composer update`. Once composer is finished, add the service provider to the `providers` array in `app/config/app.php`:
```
Chalcedonyt\QueryBuilderTemplate\Providers\QueryBuilderTemplateServiceProvider::class
```

## Usage

__Templates__ store the structure of the query - The base tables and columns.
__Scopes__ modify the Query Builders that are passed to them.

In template factories, fill in a base query (i.e. DB::table() -> select() ) to be chained off. Then fill in optional joins that will be made if requested by a scope.

```php
protected function getAvailableJoinClauses(){
    //join with the user table
    $user_join = new JoinClause('inner','test_users');
    $user_join -> on('test_users.id','=','test_posts.user_id');

    return [
        $user_join
    ];
}
```
These joins will be chained if any scope from the `test_users` table is added.

In scopes, fill in the `$tables` parameter with an array of table names that a calling template should join.

The `setup()` function will be called before the template is executed (useful for TEMPORARY TABLE, for example).

```php
public function setup()
{
    //DB::statement('CREATE TEMPORARY TABLE ...');
}
```
Finally, the `apply()` function should be used to modify the query builder chain.
```php
public function apply( \Illuminate\Database\Query\Builder $query )
{
    //modify $query here
    if( $this -> minAge )
        $query -> where('test_users.dob','<=', Carbon::now() -> subYears($this -> minAge) -> toDateTimeString() );
    if( $this -> maxAge )
        $query -> where('test_users.dob','>=', Carbon::now() -> subYears($this -> maxAge) -> toDateTimeString() );
    return $query;
}
```

## Generating Templates
An artisan command will be added to quickly create query templates.
``` php
php artisan query:make:template [TemplateName]
```

## Generating Scopes

An artisan command will be added to quickly create scopes.
``` php
php artisan query:make:scope [ScopeName]
```
Adding a `--parameters` flag will prompts for parameters to be inserted into the constructor when generated.

## Binding Templates
You may also conveniently bind any templates that you create. To do this, publish the package:
```
php artisan vendor:publish
```
This will create a `App\Providers\QueryBuilderTemplateServiceProvider` file. Insert any templates you have created here and they will be bound to the app. Remember to add `App\Providers\QueryBuilderTemplateServiceProvider:class` in `config/app.php`.

```php
class QueryBuilderTemplateServiceProvider extends ServiceProvider
{
    /**
     * The resolvers
     *
     * @var array
     */
    protected $templates = [
        'MyTemplate' => \App\QueryBuilderTemplate\Templates\Factory\MyTemplate::class,
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
```

```php
$my_template = app() -> make('MyTemplate');
```


## Examples
``` php
class UserAgeBetweenScope extends AbstractScope
{
    /**
     * @var  Array tables that are used by this query.
     */
    protected $tables = ['test_users'];

    /**
    * @var
    */
    protected $minAge;

    /**
    * @var
    */
    protected $maxAge;

    /**
    *
    *  @param   $ageMin
    *  @param   $ageMax
    *
    */
    public function __construct($min_age, $max_age)
    {
        $this -> minAge = $min_age;
        $this -> maxAge = $max_age;
    }

    /**
     * Customize this to define how queries should be set up.
     */
    public function setup()
    {
        //DB::statement('CREATE TEMPORARY TABLE ...');
        //populate a cache
    }

    /**
     * Modify a Builder object. Changes here can be nested
     * @param  \Illuminate\Database\Query\Builder $query
     * @return  \Illuminate\Database\Query\Builder $query
     */
    public function apply( \Illuminate\Database\Query\Builder $query )
    {
        //modify $query here
        if( $this -> minAge )
            $query -> where('test_users.dob','<=', Carbon::now() -> subYears($this -> minAge) -> toDateTimeString() );
        if( $this -> maxAge )
            $query -> where('test_users.dob','>=', Carbon::now() -> subYears($this -> maxAge) -> toDateTimeString() );
        return $query;
    }

}
```



```php
class PostIsMaxDaysOldScope extends AbstractScope
{
    /**
     * @var  Array tables that are used by this query.
     */
    protected $tables = ['test_posts'];

    /**
    * @var
    */
    protected $daysOld;

    /**
    *
    *  @param   $daysOld
    *
    */
    public function __construct($days_old)
    {
        $this -> daysOld = $days_old;
    }

    /**
     * Modify a Builder object. Changes here can be nested
     * @param  \Illuminate\Database\Query\Builder $query
     * @return  \Illuminate\Database\Query\Builder $query
     */
    public function apply( \Illuminate\Database\Query\Builder $query )
    {
        $date =date('Y-m-d H:i:s', strtotime('-'.$this -> daysOld.' day'));
        return $query -> where('test_posts.created_at','<=', $date);
    }

}
```

```php
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
        //join with the survey table
        $user_join = new JoinClause('inner','test_users');
        $user_join -> on('test_users.id','=','test_posts.user_id');

        return [
            $user_join
        ];
    }
}
```
```php
//returns all Posts
$factory = new PostTemplateFactory();
$template = $factory -> create();

$young_scope = new UserAgeBetweenScope(20, 30);
$template -> addRequired($young_scope);
//all Posts with Users between 20 and 30 years of age. Automatically joins the user table.
$query = $template -> getBuilder();

//all Posts within the past week with Users between 20 and 30 years of age
$post_age_scope = new PostIsMaxDaysOldScope(7);
$template -> addRequired($post_age_scope);
$query = $template -> getBuilder();
```

## Adding A Scope to the Template

As illustrated by the above example, a scope can be added to the template factory with:
* **$template -> addRequired( $scope );**
    
Sub-queries will be wrapped in a 'WHERE ... AND' block
```
SELECT ... WHERE ("test_users"."dob" <= ? AND "test_users"."dob" >= ?) AND 
("test_users"."gender" = ?) AND 
("test_posts"."created_at" <= ?)
```

Additionally, there are 2 more available options which can be used:
* **$template -> addOptional( $scope );**
    
Sub-queries will be wrapped in a 'WHERE ... OR' block
```
SELECT ... WHERE ("test_users"."dob" <= ? AND "test_users"."dob" >= ?) AND 
(
    ("test_users"."gender" = ?) OR ("test_posts"."created_at" <= ?)
)
```    
    
* **$template -> addDirect( $scope );**

Use this if you decided to run some clauses such as 'GROUP BY ... HAVING' in your Scope class, for example:
```
public function apply( \Illuminate\Database\Query\Builder $query )
{        
        return $query -> groupBy('test_users.id')
                      -> having('test_posts.views', '>=', $this -> views);
}
``` 
The generated SQL query would be:
```
SELECT ... FROM ...
GROUP BY `test_users`.`id` 
HAVING `test_posts`.`views` >= ?
```
       
## Change log

* 0.1 Initial attempt. OR queries still not working very well.
* 0.11 Renamed generate() to getBuilder();
* 0.22 Remove --directory and --namespace options when generating Templates/Scopes. You can now create a Template or Scope inside a directory by specifying it in the classname, e.g. `php artisan query:make:scope MyScopeDir\\MyScope`

## Roadmap

* Add blocks for "OR" and "AND".
* Add solution for inverting scopes ("NOT")

## Credits

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
