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

Do not forget to create the followings directory, these needs to exist and be
configured in `config/config.ini`!
- export
- log
- metadata
- acl
- convert
(It is possible to use the same directory for exportDir, logDir, metadataDir, aclDir and convertDir)

# Usage

## Export
The export function will create a json based export of the JANUS reguistry data, including entity metadata, SCL and ARP information.

You can run the `export.php` script after you configured everything.

    $ php export.php

If you want to view the export in a formatted way you can use Python:

    $ cat export/export.json | python -mjson.tool | less

## Metadata
The metadata function will fetch all remote metadata for all registered entities that have a metdata URL configured in teh registry

You can fetch the metadata from the metadata URLs available from the export
data.

    $ php metadata.php

## Validation
The validate function will compare exported registry data with the metadata as was downloaded from the remote metadata YRL for each entity

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

## ACL
You can generate the ACL list as a JSON file using the ACL tool:

    $ php aclDump.php

This will write the `acl.json` file to the export directory.

## Convert to simpleSAMLphp
The `export.json` file can be converted to a simpleSAMLphp compatible file by
running the `convert.php` script:

    $ php convert.php

It will write two files: `saml20-idp-remote.json` and `saml20-sp-remote.json`
containing the IdP and SP configuration.

## Mail
In order to mail errors to email you can run the `mail.php` script:

    $ php mail.php

You can configure the addresses in `config/config.ini`.

# Cron
In order to automatically run the scripts, the following cron is suggested:

    33 3 * * * php /home/fkooman/janus-tools/metadata.php
    0 * * * * php /home/fkooman/janus-tools/export.php && php /home/fkooman/janus-tools/validate.php >/dev/null && php /home/fkooman/janus-tools/aclDump.php
    0 4 * * mon php /home/fkooman/janus-tools/mail.php >/dev/null

This will run the most scripts every hour, and the metadata fetching at 3:33 AM
which gives it half an hour to complete before the other scripts run again. In
addition this will once a week, on Monday morning at 4am mail a log to the
configured addresses in `config/config.ini`.

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
