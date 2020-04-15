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
            // ->addOption('tables', null, InputOption::VALUE_OPTIONAL, 'Specify the table names?')

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // Bring the schema
        $name = $input->getArgument('name');

        // Valid?
        if( !preg_match('~^([a-z/\\\]+)$~i', $name) )
        {
            // Message
            $output->writeln('<error>Invalid controller name</error>');
        }

        return 0;
    }
}