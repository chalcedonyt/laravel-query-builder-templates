<?php
namespace Chalcedonyt\QueryBuilderTemplate\Scopes;

class ScopeFactory
{
	/**
     * @var The class name of the scope to be built by this factory
     */
	protected $className;

	/**
     * @var The arguments to be passed to the constructor of the new instance created from the given class
     */
	protected $arguments;

	/**	 
	 *  @param  string $className 
	 *  @param  array  $arguments	 
	 */
	public function __construct( $className, $arguments )
	{
		$this -> className = $className;
		$this -> arguments = $arguments;
	}

	/**
	 * Create a new scope instance
     * @return An instance of the scope class specified in the constructor
	 */
	public function create()
	{
		$scope = new \ReflectionClass( $this -> className );
		$new_scope = $scope -> newInstanceArgs( $this -> arguments );
		return $new_scope;
	}
}
?>