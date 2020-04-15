<?php
namespace System\Core\Zeus;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use System\Core\Database\Iguana;

use App\Database\Schema;

class DbReload extends Command
{
    protected static $defaultName = 'db:reload';

    protected function configure()
    {
        $this
            ->setDescription('Clean all the tables and re-seed them.')
            ->setHelp('This command allows you to delete all of the rows in your tables defined in [App\Database\Schema] class and re-seed.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getApplication()->find('db:clean')->run($input, $output);
        $this->getApplication()->find('db:seed')->run($input, $output);

        return 0;
    }
}