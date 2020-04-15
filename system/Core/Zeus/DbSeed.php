<?php
namespace System\Core\Zeus;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Database\Schema;

class DbSeed extends Command
{
    protected static $defaultName = 'db:seed';

    protected function configure()
    {
        $this
            ->setDescription('Push everything in your seeders.')
            ->setHelp('This command allows you to push rows into database that you have defined.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // Bring the schema
        $schema = with(Schema::class);

        // Push all the defined seeds
        $io->title('Seeding your tables');
        foreach($schema->seeds() as $seed)
        {
            $output->writeln('Seeding:     [<fg=yellow>'.$seed.'</>]');
            
            try {
                with($seed)->call();
                $output->writeln('Successful:  [<fg=yellow>'.$seed.'</>]');
            } catch (Exception $e) {
                $output->writeln('<bg=red>Failed:</>      [<fg=yellow>'.$seed.'</>]');
            }
        }

        return 0;
    }
}