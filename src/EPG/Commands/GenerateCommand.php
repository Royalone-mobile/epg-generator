<?php
namespace EPG\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use EPG\Providers\TelestarProvider;
use XMLTV\Xmltv;

class GenerateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('epg:generate')
            ->setDescription('Generate EPG.')
            ->setHelp('This command allows you to generate the EGP XML file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $provider = new TelestarProvider();
        $packages = $provider->fetch_packages();

        $epg_channels = [];
        foreach ($packages as $package) {
            $channels = $provider->fetch_channels($package->id);
            foreach ($channels as $channel) {
                if (!isset($epg_channels[$channel->id])) {
                    $channel->programs = $provider->fetch_channel_programs($package->id, $channel->id);
                    $epg_channels[$channel->id] = $channel;
                }
            }
        }

        $xmltv = new Xmltv();
        $xmltv
            ->setDate(date('Y-m-d'))
            ->setSourceinfourl('https://b-alidra.com/xmltv')
            ->setSourceinfoname('b-alidra.com')
            ->setSourcedataurl('https://b-alidra.com/xmltv')
            ->setGeneratorinfoname('XMLTV')
            ->setGeneratorinfourl('https://b-alidra.com/xmltv');

        foreach ($epg_channels as $epg_channel) {
            $provider->add_xmltv_channel($xmltv, $epg_channel);
            foreach ($epg_channel->programs as $epg_program) {
                $provider->add_xmltv_program($xmltv, $epg_channel, $epg_program);
            }
        }

        $xmltv->validate();

        echo $xmltv->toXml();
    }
}
