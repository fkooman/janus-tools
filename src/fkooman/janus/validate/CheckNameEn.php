<?php

namespace fkooman\janus\validate;

class CheckNameEn extends Validate implements ValidateInterface
{

    public function sp($entityData, $metadata, $allowedEntities, $blockedEntities, $arp)
    {
        if (!isset($metadata['name']['en'])) {
            $this->logWarn("no english name set");
        }
    }

    public function idp($entityData, $metadata, $allowedEntities, $blockedEntities, $disableConsent)
    {
        if (!isset($metadata['name']['en'])) {
            $this->logWarn("no english name set");
        }
    }
}
