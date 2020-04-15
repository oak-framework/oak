<?php
namespace System\Core\Zeus;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use System\Core\Database\Iguana;

class DbNuke extends Command
{
    protected static $defaultName = 'db:nuke';

    protected function configure()
    {
        $this
            ->setDescription('Destroy your database [WARNING]')
            ->setHelp('This command lets you destroy everything in your database. Be careful, literally EVERYTHING!');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        // Ask
        // $question = new ConfirmationQuestion('This will delete everything in your database including your non-related tables to your project. If you are not sure what this means, better not to say yes. Do you confirm your action? [Y\N]', 'n');
        $question = new ConfirmationQuestion('<fg=red>Do you confirm your action?</> [Y\N] ', 'n');

        $question->setNormalizer(function ($value) {
            $value = $value ? trim($value) : '';

            $value = preg_replace('~^(yes|y|evet|e|ok|1|true|\+)$~i', 'y', $value);
            return $value;
        });

        // Ask and apply
        if( $helper->ask($input, $output, $question) !== 'y' )
        {
            // Message
            $output->writeln('<comment>You may have saved your a** just a sec ago!</comment>');
            return 0;
        }
        
        // Nuke
        Iguana::nuke();

        // Message
        $output->writeln('<comment>Everything is gone!</comment>');
        
        return 0;
    }
}