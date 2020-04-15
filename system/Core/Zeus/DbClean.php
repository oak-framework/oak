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

class DbClean extends Command
{
    protected static $defaultName = 'db:clean';

    protected function configure()
    {
        $this
            ->setDescription('Clean your table content.')
            ->setHelp('This command allows you to clean your tables defined in [App\Database\Schema] class.')

            ->addOption('tables', null, InputOption::VALUE_OPTIONAL, 'Specify the table names?')
            // ->addArgument('tables', InputArgument::REQUIRED, 'Specify table names')

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // Bring the schema
        $schema = with(Schema::class);

        // Which tables to clean?
        if( empty($input->getOption('tables')) )
        {
            $requestedTables = $schema->tables();
        } else {
            $requestedTables = array_map('trim', explode(',', $input->getOption('tables')));
        }

        // Push all the defined seeds
        $rows = [];
        $io->title('Cleaning table rows');
        foreach($requestedTables as $table)
        {
            $io->writeln("Cleaning...     [<fg=yellow>{$table}</>]");
            if( Iguana::wipe( $table ) )
                $io->writeln("Table cleaned   [<fg=yellow>{$table}</>]");
            else
                $io->writeln("<bg=red>Cleaning failed</> [<fg=yellow>{$table}</>]");
        }

        return 0;
    }
}