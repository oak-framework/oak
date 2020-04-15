<?php
namespace App\Zeus\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    protected static $defaultName = 'test';

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('A test.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command just tests whether application commands are working or not.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arr = [
            'All good ;)',
            'Zeus is on fire!',
            'Did someone call Zeus?',
            'Oops, Zeus is sleeping :( zzZZ',
            'I am not an A.I.',
        ];
        $output->write($arr[array_rand($arr)]);

        return 0;
    }
}