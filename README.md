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

If you want to view the export in a formatted way you can use Python:

    $ cat export/export.json | python -mjson.tool | less
    
## Validation
You can validate the export you just made using `validate.php`.

    $ php validate.php
    
This script uses the export written by `export.php` and creates a log file in
the `export` directory called `log.json`.

You can add more checks by writing a class yourself implementing such a check
and enabling it in the configuration file, see `config/config.ini.defaults` for
an example. See the included validation classes for inspiration on how to do
this.

You can also use Python here to view the log somewhat formatted:

    $ cat export/log.json | python -mjson.tool

# Validation Filters
You can add your own validation filters to 
`src/SURFnet/janus/validate/validators`. Copy one of the other validators to 
get started and modify it as needed.

You must implement two methods: `idp` and `sp` with their respective parameters.
You can implement your check using the data that is made available as 
parameters to the methods. If you are writing a filter only for IdPs or for SPs
you can leave the body of the other type empty. You can write log entries 
using for example:

    $this->logWarning("sp must have arp");
    
There is also the option to use `$this->logError("msg");`. The context of the 
entity is saved as well: the entity ID of the entity, the entity type, i.e.:
`saml20-idp` or `saml20-sp` and the module that generates the message.

# License
Licensed under the Apache License, Version 2.0;

   http://www.apache.org/licenses/LICENSE-2.0
