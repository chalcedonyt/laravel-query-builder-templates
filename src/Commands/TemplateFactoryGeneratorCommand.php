<?php namespace Chalcedonyt\QueryBuilderTemplate\Commands;

use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem as File;
use Illuminate\View\Factory as View;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class SpecificationGeneratorCommand
 *
 * @package Chalcedony\Specification\Commands
 */
class TemplateFactoryGeneratorCommand extends Command
{

    const NO_CLASS_SPECIFIED = 'mixed';
    const NO_PARAMETER_SPECIFIED = '(no_param)';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'query:make:template {classname} {--parameters : Whether to create a factory with parameters}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new QueryBuilderTemplate\Template\Factory class';
    /**
     * @var
     */
    private $view;
    /**
     * @var
     */
    private $namespace;
    /**
     * @var
     */
    private $directory;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var File
     */
    private $file;

    /**
     * @param View $view
     */
    function __construct(Config $config, View $view, File $file)
    {
        parent::__construct();
        $this->config = $config;
        $this->view = $view;
        $this->file = $file;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            // replace all space after ucwords
            $classname = preg_replace('/\s+/', '', ucwords($this->argument('classname')));
            $namespace = $this->config->get('query_builder_template.namespace').'\\Templates\\Factory';
            $directory = $this->appPath($this->config->get('query_builder_template.directory')).DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'Factory';

            //retrieves store directory configuration
            if( strpos($classname, '\\') !== false ){
                $class_dirs = substr($classname, 0, strrpos( $classname, '\\'));
                $directory = $directory.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $class_dirs);
                $namespace = $namespace.'\\'.$class_dirs;
                $classname = substr($classname, strrpos($classname, '\\') + 1);
            }


            is_dir($directory) ?: $this->file->makeDirectory($directory, 0755, true);

            $create = true;
            $parameters = collect([]);
            $parameter_string = '';
            /**
             * if we are entering paramters
             */
            if( $this -> option('parameters')){

                $i = 0;
                while($parameter = $this -> ask("Enter the class or name for parameter ".($i++)." (Examples: \App\User or \$value) [Blank to stop entering parameters]", self::NO_PARAMETER_SPECIFIED)){
                    if( $parameter == self::NO_PARAMETER_SPECIFIED )
                        break;

                    //if class starts with $, don't type hint
                    if( strpos($parameter, '$') === 0 ){
                        $parameter_class = null;
                        $parameter_name = str_replace('$','',$parameter);
                    } else{
                        /**
                         * Extract the last element of the class after "\", e.g. App\User -> $user
                         */
                        $derive_variable_name = function() use ($parameter){
                            $parts = explode("\\", $parameter);
                            return end( $parts );
                        };
                        $parameter_class = $parameter;
                        $parameter_name = strtolower( $derive_variable_name() );
                    }
                    $parameters -> push(['class' => $parameter_class, 'name' => $parameter_name]);
                }

                if( $parameters -> count())
                {
                    $parameter_string_array = [];
                    $parameters -> each(function( $p ) use( &$parameter_string_array){
                        if( $p['class'])
                            $parameter_string_array[]=$p['class'].' $'.$p['name'];
                        else
                            $parameter_string_array[]='$'.$p['name'];
                    });
                    $parameter_string = implode(', ', $parameter_string_array);
                }
            }
            if ($this->file->exists("{$directory}/{$classname}.php")) {
                if ($usrResponse = strtolower($this->ask("The file ['{$classname}'] already exists, overwrite? [y/n]",
                    null))
                ) {
                    switch ($usrResponse) {
                        case 'y' :
                            $tempFileName = "{$directory}/{$classname}.php";

                            $prefix = '_';
                            while ($this->file->exists($tempFileName)) {
                                $prefix .= '_';
                                $tempFileName = "{$directory}/{$prefix}{$classname}.php";
                            }
                            rename("{$directory}/{$classname}.php", $tempFileName);
                            break;
                        default:
                            $this->info('No file has been created.');
                            $create = false;
                    }
                }

            }
            $args = ['namespace' => $namespace,
            'classname' => $classname,
            'parameter_string' => $parameter_string,
            'parameters' => $parameters -> all() ];

            // loading template from views
            $view = $this->view->make('query_builder_template::template_factory',$args);


            if ($create) {
                $this->file->put("{$directory}/{$classname}.php", $view->render());
                $this->info("The class {$classname} generated successfully.");
            }


        } catch (\Exception $e) {
            $this->error('Template factory creation failed: '.$e -> getMessage());
        }


    }

    /**
     * get application path
     *
     * @param $path
     *
     * @return string
     */
    private function appPath($path)
    {
        return base_path('/app/' . $path);
    }

    /**
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('name', InputArgument::REQUIRED, 'Name of the modifer class'),
        );
    }

    /**
     * @return array
     */
    protected function getOptions()
    {

        return array(
            array(
                'directory',
                null,
                InputOption::VALUE_OPTIONAL,
                'modifer store directory (relative to App\)',
                null
            ),
            array(
                'namespace',
                null,
                InputOption::VALUE_OPTIONAL,
                'modifer namespace',
                null
            ),
        );
    }


}
