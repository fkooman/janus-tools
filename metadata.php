<?php

/**
 * Copyright 2013 François Kooman <francois.kooman@surfnet.nl>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once 'vendor/autoload.php';

use Guzzle\Http\Client;

try {
    $configFile = __DIR__ . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "config.ini";
    $config = \fkooman\Config\Config::fromIniFile($configFile);

    // data directory
    $exportDir      = $config->s('output')->l('exportDir', true); // REQ
    $metadataDir    = $config->s('output')->l('metadataDir', true); // REQ

    // remove all files from metadataDir
    foreach (glob($metadataDir . "/*") as $f) {
        unlink($f);
    }

    $inputFile = $exportDir . DIRECTORY_SEPARATOR . "export.json";
    $exportData = @file_get_contents($inputFile);
    if (false === $exportData) {
        throw new Exception(sprintf("unable to read JSON file '%s' from disk", $inputFile));
    }
    $entities = json_decode($exportData, true);

    foreach ($entities as $type => $entity) {
        $metadataUrl = $entity['entityData']['metadataurl'];

        // create a hashed file name
        $fileName = $metadataDir . DIRECTORY_SEPARATOR . md5($metadataUrl) . ".xml";

        if (!file_exists($fileName)) {
            $md = fetchMetadata($metadataUrl);
            if (false === @file_put_contents($fileName, $md)) {
                throw new Exception(sprintf("unable to write metadata to file '%s'", $fileName));
            }
        }
    }

} catch (Exception $e) {
    echo sprintf("ERROR: %s", $e->getMessage());
    die(PHP_EOL);
}

function fetchMetadata($metadataUrl)
{
    try {
        $client = new Client($metadataUrl, array(
            'curl.options'   => array(CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_TIMEOUT => 15),
        ));
        $request = $client->get();
        $response = $request->send();

        return $response->getBody();
    } catch (Exception $e) {
        // if we were unable to retrieve metadata, just return false so an
        // empty file is stored
        return false;
    }
}
