<?php
namespace EPG\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use EPG\Providers\TelestarProvider;

class ListPackagesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('epg:packages')
            ->setDescription('List available channel packages.')
            ->setHelp('This command allows you to list the available channel packages.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $provider = new TelestarProvider();
        $packages = $provider->fetch_packages();

        $table = new Table($output);
        $table
            ->setHeaders(array('ID', 'Package'))
            ->setRows(
                array_map(function ($package) {
                    return [ $package->id, $package->name ];
                }, $packages)
            );
        $table->render();
    }
}
