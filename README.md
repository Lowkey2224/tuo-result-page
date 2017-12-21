Tyrant unleashed optimizer Result Page
=========
[![Build Status](https://travis-ci.org/Lowkey2224/tuo-result-page.svg?branch=master)](https://travis-ci.org/Lowkey2224/tuo-result-page)
[![Dependency Status](https://www.versioneye.com/user/projects/58c3e32262d6020040aec79e/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/58c3e32262d6020040aec79e)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Lowkey2224/tuo-result-page/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Lowkey2224/tuo-result-page/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Lowkey2224/tuo-result-page/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Lowkey2224/tuo-result-page)
[![Build Status](https://scrutinizer-ci.com/g/Lowkey2224/tuo-result-page/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Lowkey2224/tuo-result-page/build-status/master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/89a3a6d0-27b9-4d9b-b801-5e854af0b1f7/mini.png)](https://insight.sensiolabs.com/projects/89a3a6d0-27b9-4d9b-b801-5e854af0b1f7)

This is a Symfony Project, designed in use with the [Tyrant unleashed Optimizer](https://sourceforge.net/p/tyrant-unleashed-optimizer/).

It works as Management page for Players and their Cards.
To setup the application run
`composer install` and fill in your data.

next you should run 
 ```
 php bin/console doctrine:schema:create
 php bin/console fos:user:create admin --super-admin #With Admin being your username
 ```

Your next Step should be providing the Website with all viable Cards. 
For this you need to download the tyrant card files using tuo. 

When done you can import them into the Databse using
```#bash
php bin/console loki:tuo:cards:read /path/to/xml/folder
php bin/console loki:tuo:cards:import
```
As well you need to import the Global battleground effects:
```#bash
php bin/console loki:tuo:bge:import /path/to/bges.txt
```

Now you are good To go.


 - Updating ocramius/package-versions (1.1.3 => 1.2.0): Downloading (100%)         
  - Updating symfony/symfony (v3.3.13 => v3.4.2): Downloading (100%)         
  - Updating doctrine/orm (v2.5.12 => v2.5.14): Downloading (100%)         
  - Updating doctrine/doctrine-bundle (1.8.0 => 1.8.1): Downloading (100%)         
  - Updating sensio/framework-extra-bundle (v3.0.28 => v3.0.29): Downloading (100%)         
  - Updating friendsofsymfony/user-bundle dev-master (8fa15f4 => 5d1c3ff):  Checking out 5d1c3ff4fd
  - Updating tightenco/collect (v5.5.20 => v5.5.27): Downloading (100%)         
  - Updating friendsofphp/php-cs-fixer (v2.8.1 => v2.9.0): Downloading (100%)         
  - Updating php-amqplib/rabbitmq-bundle (v1.14.0 => v1.14.2): Downloading (100%)         
  - Updating friendsofsymfony/jsrouting-bundle (2.0.0 => 2.1.1): Downloading (100%)         
  - Updating sensio/generator-bundle (v3.1.6 => v3.1.7): Downloading (100%)         
  - Updating symfony/phpunit-bridge (v3.3.13 => v3.4.2): Downloading (100%)         
  - Updating phpunit/php-file-iterator (1.4.2 => 1.4.5): Downloading (100%)         
  - Updating phpunit/php-token-stream (2.0.1 => 2.0.2): Downloading (100%)         
  - Updating phpdocumentor/reflection-docblock (4.1.1 => 4.2.0): Downloading (100%)         
  - Updating phpspec/prophecy (v1.7.2 => 1.7.3): Downloading (100%)         
  - Updating phpunit/phpunit (5.7.25 => 5.7.26): Downloading (100%)         
  - Updating kriswallsmith/buzz (v0.15.1 => v0.15.2): Downloading (100%)         
  - Updating composer/ca-bundle (1.0.9 => 1.1.0): Downloading (100%)     