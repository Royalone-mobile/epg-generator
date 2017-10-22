[![Latest Stable Version](https://poser.pugx.org/b-alidra/xmltv/v/stable)](https://packagist.org/packages/b-alidra/xmltv)

# epg-generator
French channels EGP generator

Can be used in your project as a standard library or directly from the command line.

## Installation:

The library is [PSR-4 compliant](http://www.php-fig.org/psr/psr-4)
and the simplest way to install it is via composer:

     composer require b-alidra/epg-generator

## Usage

### Use the lib to generate XML
```php
<?php
    use EPG\Epg;
    use EPG\Providers\TeleramaProvider;
    use EPG\Providers\Sniffers\TeleramaSniffer;

    $sniffer  = new TeleramaSniffer();
    $provider = new TeleramaProvider($sniffer);

    $channel_ids  = [1, 9, 217, 13];
    $days_to_grab = 3;

    $provider
        // Filter on channels, optional
        ->filter_on_channels($channel_ids)
        // Grab 3 days, default 1
        ->set_days_to_grab($days_to_grab);

    $epg = new Epg($provider);

    $xml_guide = $epg->get_xml();
```

### CLI : Generate XML

```bash
% php vendor/bin/epg.php epg:generate --help

Usage:
  epg:generate [options]

Options:
  -p, --provider[=PROVIDER]        Which provider do you want to use ? [default: "telerama"]
  -d, --days[=DAYS]                How many days to grab ? [default: 1]
  -c, --channel_ids[=CHANNEL_IDS]  Grab only some channels (multiple values allowed)
  -h, --help                       Display this help message
  -q, --quiet                      Do not output any message
  -V, --version                    Display this application version
      --ansi                       Force ANSI output
      --no-ansi                    Disable ANSI output
  -n, --no-interaction             Do not ask any interactive question
  -v|vv|vvv, --verbose             Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  This command allows you to generate an EGP XML file.
```

```bash
% php vendor/bin/epg.php epg:generate
```
```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE tv PUBLIC "SYSTEM" "http://xmltv.cvs.sourceforge.net/viewvc/xmltv/xmltv/xmltv.dtd">
<tv date="2017-10-12" source-info-url="https://b-alidra.com/xmltv" source-info-name="b-alidra.com" source-data-url="https://b-alidra.com/xmltv" generator-info-name="XMLTV" generator-info-url="https://b-alidra.com/xmltv">
   <channel id="TF1">
     <display-name lang="fr">TF1</display-name>
     </channel>
     <channel id="IDF1">
     <display-name lang="fr">IDF1</display-name>
   </channel>
   ...
   <programme channel="TF1" start="20171012184000" stop="20171012184500" pdc-start="20171012184000" vps-start="20171012184000">
     <title lang="fr">Météo</title>
     <sub-title lang="fr"/>
     <desc lang="fr"/>
     <date>20171012</date>
     <category lang="fr">Météo</category>
     <length units="minutes">5</length>
   </programme>
   ...
 </tv>
```
### CLI : List channels

```bash
% php vendor/bin/epg.php epg:channels --help

Usage:
  epg:channels [options]

Options:
  -p, --provider[=PROVIDER]  Which provider do you want to use ? [default: "telerama"]
  -h, --help                 Display this help message
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi                 Force ANSI output
      --no-ansi              Disable ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  This command allows you to list the available channels.
```
```bash
% php vendor/bin/epg.php epg:channels
     
+----------------+------------------------------+
| ID             | Channel                      |
+----------------+------------------------------+
| TNT (4)                                       |
+----------------+------------------------------+
| 1              | TF1                          |
| 9              | France 2                     |
| 21             | France 3                     |
| 7              | Canal+                       |
| 13             | France 5                     |
| 28             | M6                           |
| 25             | Arte                         |
| 64             | C8                           |
| 29             | W9                           |
| 38             | TMC                          |
| 65             | NT 1                         |
| 63             | NRJ 12                       |
| 45             | La Chaine parlementaire      |
                 ...
```
