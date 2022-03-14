<?php

declare(strict_types = 1);

namespace App\Command\Library;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'csl:tag:load-by-playlists-for-user',
    description: 'Sends mail to all providers to check if offers are still available',
)]
class TagLibraryCommand extends Command
{

    private const USERNAME = 'username';

    public function configure()
    {
        $this->addArgument(self::USERNAME, InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $userName = $input->getArgument(self::USERNAME);

        dd($userName);
    }
}