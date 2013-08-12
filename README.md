# Introduction
This tool exports JANUS data to JSON for both export purposes and checking the
data to see if the data contained within JANUS is valid.

# Installation
You can use [Composer](http://getcomposer.org/) to install the dependencies.
First install Composer:

    $ curl -O http://getcomposer.org/composer.phar

Then install the dependencies:

    $ php composer.phar install

# Configuration
Copy the `config/config.ini.defaults` to `config/config.ini` and modify it for
your setup, i.e.: set the database information and (export) paths. See the
explanation included in the template `config.ini.defaults` on what everything
means.

Do not forget to create the `export` directory, it needs to exist and be
configured in `config/config.ini`!

# Usage
You can run the `export.php` script after you configured everything.

    $ php export.php

