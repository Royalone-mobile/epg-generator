<?php
namespace EPG\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use EPG\Epg;
use EPG\Providers\Provider;
use EPG\Providers\TeleramaProvider;
use EPG\Providers\Sniffers\TeleramaSniffer;
use EPG\Providers\TelestarProvider;
use EPG\Providers\Sniffers\TelestarSniffer;

class GenerateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('epg:generate')
            ->setDescription('Generate EPG.')
            ->setHelp('This command allows you to generate an EGP XML file.')
            ->addOption(
                'provider',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Which provider do you want to use ?',
                'telerama'
            )
            ->addOption(
                'days',
                'd',
                InputOption::VALUE_OPTIONAL,
                'How many days to grab ?',
                1
            )
            ->addOption(
                'channel_ids',
                'c',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Grab only some channels',
                []
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $provider     = $input->getOption('provider');
        $channel_ids  = $input->getOption('channel_ids');
        $days_to_grab = $input->getOption('days');

        switch ($provider) {
            case Provider::TELERAMA_PROVIDER:
                $sniffer  = new TeleramaSniffer();
                $provider = new TeleramaProvider($sniffer);
                break;
            case Provider::TELESTAR_PROVIDER:
                $sniffer  = new TelestarSniffer();
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

        $provider
            ->filter_on_channels($channel_ids)
            ->set_days_to_grab($days_to_grab);

        $epg = new Epg($provider);

        $output->write($epg->get_xml());
    }
}
