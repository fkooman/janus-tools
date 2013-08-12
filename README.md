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

## Export
You can run the `export.php` script after you configured everything.

    $ php export.php

## Validation
You can validate the export you just made using `validate.php`.

    $ php validate.php
    
This script uses the export written by `export.php` and creates a log file in
the `export` directory called `log.json`.

You can add more checks by writing a class yourself implementing such a check
and enabling it in the configuration file, see `config/config.ini.defaults` for
an example. See the included validation classes for inspiration on how to do
this.