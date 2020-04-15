<?php
namespace System\Core\Zeus;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use System\Core\Database\Iguana;

use App\Database\Schema;

class MakeController extends Command
{
    protected static $defaultName = 'make:controller';

    protected function configure()
    {
        $this
            ->setDescription('Clean your table content.')
            ->setHelp('This command allows you to clean your tables defined in [App\Database\Schema] class.')

            ->addArgument('name', InputArgument::REQUIRED, 'Specify controller name')
            // ->addOption('force', null, InputOption::VALUE_OPTIONAL, 'Force?')

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // Bring the schema
        $name = $input->getArgument('name');

        // Valid?
        if( !preg_match('~^([^0-9\_/\\\][a-z0-9\_/\\\]+?)$~i', $name) )
        {
            // Message
            $output->writeln('<error>Invalid controller name</error>');

            return 0;
        }

        // Slashes to backslashes
        $name = str_replace('/', '\\', $name);

        // Do we have it already?
        if( class_exists(str_start($name, 'App\\Controllers\\'))  )
        {
            // Message
            $output->writeln('<error>Controller already exists ['.str_start($name, 'App\\Controllers\\').']</error>');

            return 0;
        }

        // Good to go!
        $file = APP_PATH.'/Controllers/' . $name . '.php';
        $code = str_replace(['{namespace}', '{name}'], [
            implode('\\', array_slice(explode('\\', str_start($name, 'App\\Controllers\\')), 0, -1)),
            class_basename($name)
        ], $this->controllerCode());

        // Make dir if doesn't exist
        $dir = APP_PATH.'/Controllers/'.implode('/', array_slice(explode('\\', $name), 0, -1));
        if( !is_dir($dir) )
            mkdir( $dir );

        file_put_contents($file, $code);
        

        // Message
        $output->writeln('<info>Controller is ready under ['.$dir.']</info>');

        return 0;
    }

    /**
     * Controller code
     */
    public function controllerCode()
    {
        return <<<PHP
<?php
namespace {namespace};
use System\Core\Controllers\Controller;

class {name} extends Controller
{
    /**
     * This is your very first method
     * 
     * @return Response
     */
    public function index()
    {
        return 'Hello World';
    }
}
PHP;
    }
}