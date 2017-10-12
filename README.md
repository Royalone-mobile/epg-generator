[![Latest Stable Version](https://poser.pugx.org/b-alidra/xmltv/v/stable)](https://packagist.org/packages/b-alidra/xmltv)

# epg-generator
French channels EGP generator

Can be used in your project as a standard library or directly from the command line.

## Installation:

The library is [PSR-4 compliant](http://www.php-fig.org/psr/psr-4)
and the simplest way to install it is via composer:

     composer require b-alidra/epg-generator

## Usage

### CLI : Generate XML

     $> php vendor/bin/epg.php epg:generate

Result:
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

### CLI : List packages

     $> php vendor/bin/epg.php epg:packages

Result:
  
     +----+----------------------------+
     | ID | Package                    |
     +----+----------------------------+
     | 4  | TNT                        |
     | 7  | Free                       |
     | 6  | Orange                     |
     | 2  | Numericable                |
     | 8  | SFR                        |
     | 11 | Bouygues                   |
     | 1  | Canalsat                   |
     | 3  | VOO                        |
     | 9  | Proximus                   |
     | 10 | Numericable Belux          |
     | 12 | Bis TV                     |
     | 13 | TNT Sat                    |
     | 14 | Numéricâble OMT            |
     | 15 | Cablecom (Suisse)          |
     | 16 | Telenet                    |
     | 17 | Scarlet                    |
     | 18 | Wolu TV                    |
     | 19 | TéléSAT                    |
     | 20 | Bis Télévisions (Belgique) |
     +----+----------------------------+
     
 ### CLI : List channels

Result:

     $> php vendor/bin/epg.php epg:channels
     
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
     | 45             | La Chaîne parlementaire      |
                      ...
                      
 ### Use the lib to generate XML
 ```php
      <?php
      
      use EPG\Providers\TelestarProvider;
      use XMLTV\Xmltv;
      
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

      // See https://github.com/b-alidra/XMLTV-Generator/blob/master/README.md for the XMLTV part
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

      // Validate generated XML against DTD
      $xmltv->validate();

      // Get the generated XML as string
      $xml_guide = $xmltv->toXml();
   ```   
      
