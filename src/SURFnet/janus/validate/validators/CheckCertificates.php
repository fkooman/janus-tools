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
use fkooman\X509\CertParser;
use fkooman\X509\CertParserException;

class CheckCertificates extends Validate implements ValidateInterface
{
    // 60*60*24*14
    const EXPIRY_WARNING_TIME = 1209600;

    public function idp($entityData, $metadata, $allowedEntities, $blockedEntities, $disableConsent)
    {
        $this->validateCertificates($metadata);
    }

    public function sp($entityData, $metadata, $allowedEntities, $blockedEntities, $arp)
    {
        $this->validateCertificates($metadata);
    }

    private function validateCertificates(array $metadata)
    {
        foreach (array("certData", "certData2") as $i) {
            if (isset($metadata[$i])) {
                $this->validateCertificate($i, $metadata[$i]);
            }
        }
        if (isset($metadata['keys'])) {
            foreach ($metadata['keys'] as $k => $v) {
                $this->validateCertificate($k, $v['X509Certificate']);
            }
        }
    }

    private function validateCertificate($i, $c)
    {
        try {
            $c = new CertParser($c);
            $expiresAt = $c->getNotValidAfter();
            if (time() > $expiresAt) {
                $this->logWarn(sprintf("certificate '%s' expired at %s [%s]", $c->getName(), date("r", $expiresAt), $i));
            } elseif (time() + self::EXPIRY_WARNING_TIME > $expiresAt) {
                $this->logWarn(sprintf("certificate '%s' expires at %s [%s]", $c->getName(), date("r", $expiresAt), $i));
            }
        } catch (CertParserException $e) {
            $this->logWarn(sprintf("%s [%s]", $e->getMessage(), $i));
        }

    }

}
