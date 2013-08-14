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

namespace SURFnet\janus\validate\validators;

use SURFnet\janus\validate\Validate;
use SURFnet\janus\validate\ValidateInterface;
use fkooman\saml\metadata\Parser;
use fkooman\saml\metadata\ParserException;
use fkooman\X509\CertParser;

class CheckIdpMetadataSigningCertificates extends Validate implements ValidateInterface
{
    public function sp(array $entityData, array $metadata, array $allowedEntities, array $blockedEntities, $arp)
    {
    }

    public function idp(array $entityData, array $metadata, array $allowedEntities, array $blockedEntities, array $disableConsent)
    {
        $metadataDir = $this->globalConfig->s('output')->l('metadataDir', true);
        $metadataUrl = $entityData['metadataurl'];
        $metadataFile = $metadataDir . DIRECTORY_SEPARATOR . md5($metadataUrl) . ".xml";
        $entityId = $entityData['entityid'];

        try {
            $parser = new Parser($metadataFile);
            $remoteMetadata = $parser->getIdp($entityId);

            $remoteMetadataKeys = $this->extractSigningKeys($remoteMetadata);
            $janusKeys = $this->extractSigningKeys($metadata);

            foreach ($remoteMetadataKeys as $k) {
                $found = false;
                foreach ($janusKeys as $l) {
                    if ($l->toBase64() === $k->toBase64()) {
                        $found = true;
                    }
                }
                if (!$found) {
                    $this->logErr(sprintf("signing cert '%s' from metadata URL with expiry '%s' not found in JANUS config", $k->getName(), $k->getExpiresAt()));
                }
            }
        } catch (ParserException $e) {
            // we were unable to parse the metadata
            $this->logErr(sprintf("metadata from metadata URL '%s' not available or broken", $metadataUrl));
        }
    }

    private function extractSigningKeys(array $metadata)
    {
        $signingKeys = array();
        if (isset($metadata['keys'])) {
            foreach ($metadata['keys'] as $i) {
                // JANUS export does not have the signing field when the box is not checked
                if (isset($i['signing']) && $i['signing']) {
                    array_push($signingKeys, new CertParser($i['X509Certificate']));
                }
            }
        }
        // certData/certData2 do not indicated whether they are signing or
        // encryption certificates, so assume they are used for signing as well
        foreach (array("certData", "certData2") as $i) {
            if (isset($metadata[$i])) {
                array_push($signingKeys, new CertParser($metadata[$i]));
            }
        }

        return $signingKeys;
    }

}
