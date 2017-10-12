<?php
namespace EPG\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableCell;
use EPG\Providers\TelestarProvider;

class ListChannelsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('epg:channels')
            ->setDescription('List available channels.')
            ->setHelp('This command allows you to list the available channels.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $provider = new TelestarProvider();
        $packages = $provider->fetch_packages();

        $rows = [];
        foreach ($packages as $package) {
            $rows[] = [ new TableCell($package->name.' ('.$package->id.')', array('colspan' => 2))];
            $rows[] = new TableSeparator();

            $channels = $provider->fetch_channels($package->id);
            foreach ($channels as $channel) {
                $rows[] = [
                    $channel->id,
                    $channel->label
                ];
            }

            $rows[] = new TableSeparator();
        }

        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'Channel'])
            ->setRows($rows)
            ->render();
    }
}
