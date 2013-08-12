<?php

namespace fkooman\janus\validate;

interface ValidateInterface
{
    public function sp($entityData, $metadata, $allowedEntities, $blockedEntities, $arp);
    public function idp($entityData, $metadata, $allowedEntities, $blockedEntities, $disableConsent);
}
