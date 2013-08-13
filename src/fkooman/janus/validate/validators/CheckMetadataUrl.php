<?php

namespace fkooman\janus\validate\validators;

use fkooman\janus\validate\Validate;
use fkooman\janus\validate\ValidateInterface;

class CheckMetadataUrl extends Validate implements ValidateInterface
{
    public function idp($entityData, $metadata, $allowedEntities, $blockedEntities, $disableConsent)
    {
        $this->validateMetadataUrl($entityData);
    }

    public function sp($entityData, $metadata, $allowedEntities, $blockedEntities, $arp)
    {
        $this->validateMetadataUrl($entityData);
    }

    private function validateMetadataUrl(array $entityData)
    {
        if (!isset($entityData['metadataurl'])) {
            $this->logWarn("no metadata URL");

            return;
        }
        $u = $entityData['metadataurl'];

        if (false === filter_var($u, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            $this->logWarn(sprintf("invalid metadata URL [%s]", $u));

            return;
        }

        if (0 !== strpos($u, "https://")) {
            $this->logWarn(sprintf("non SSL metadata URL [%s]", $u));

            return;
        }
    }
}
