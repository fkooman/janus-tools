<?php

namespace fkooman\janus\validate\validators;

use fkooman\janus\validate\Validate;
use fkooman\janus\validate\ValidateInterface;

class SurfCheckAllowAll extends Validate implements ValidateInterface
{

    public function sp($entityData, $metadata, $allowedEntities, $blockedEntities, $arp)
    {
        if ("no" === $entityData['allowedall']) {
            $this->logWarn("sp must have 'allowedall' set");
        }
    }

    public function idp($entityData, $metadata, $allowedEntities, $blockedEntities, $disableConsent)
    {
        if ("yes" === $entityData['allowedall']) {
            $this->logWarn("idp must not have 'allowedall' set");
        }
    }
}
