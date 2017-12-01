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
