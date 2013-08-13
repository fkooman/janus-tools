<?php

namespace fkooman\janus\validate\validators;

use fkooman\janus\validate\Validate;
use fkooman\janus\validate\ValidateInterface;
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
                $this->logWarn(sprintf("certificate expired at %s [%s]", date("r", $expiresAt), $i));
            } elseif (time() + self::EXPIRY_WARNING_TIME > $expiresAt) {
                $this->logWarn(sprintf("certificate expires at %s [%s]", date("r", $expiresAt), $i));
            }
        } catch (CertParserException $e) {
            $this->logWarn(sprintf("%s [%s]", $e->getMessage(), $i));
        }

    }

}
