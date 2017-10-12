<?php
namespace EPG\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableCell;
use EPG\Epg;
use EPG\Providers\Provider;
use EPG\Providers\TeleramaProvider;
use EPG\Providers\Sniffers\TeleramaSniffer;
use EPG\Providers\TelestarProvider;
use EPG\Providers\Sniffers\TelestarSniffer;

class ListChannelsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('epg:channels')
            ->setDescription('List available channels.')
            ->setHelp('This command allows you to list the available channels.')
            ->addOption(
                'provider',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Which provider do you want to use ?',
                'telerama'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $provider     = $input->getOption('provider');

        switch ($provider) {
            case Provider::TELERAMA_PROVIDER:
                $sniffer = new TeleramaSniffer();
                $provider = new TeleramaProvider($sniffer);
                break;
            case Provider::TELESTAR_PROVIDER:
                $sniffer = new TelestarSniffer();
                $provider = new TelestarProvider($sniffer);
                break;
            default:
                throw new \InvalidArgumentException(sprintf(
                    "Unknown %s provider. Use %s|%s",
                    $provider,
                    Provider::TELERAMA_PROVIDER,
                    Provider::TELESTAR_PROVIDER
                ));
        }

        $channels = $provider->get_channels();

        $rows = array_map(function ($channel) {
            return [
                $channel->id,
                $channel->displayName
            ];
        }, $channels);

        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'Channel'])
            ->setRows($rows)
            ->render();
    }
}
