<?php

namespace fkooman\janus\validate\validators;

use fkooman\janus\validate\Validate;
use fkooman\janus\validate\ValidateInterface;

class CheckContacts extends Validate implements ValidateInterface
{

    public function sp($entityData, $metadata, $allowedEntities, $blockedEntities, $arp)
    {
        if (isset($metadata['contacts'])) {
            $this->validateContacts($metadata['contacts']);
        }
    }

    public function idp($entityData, $metadata, $allowedEntities, $blockedEntities, $disableConsent)
    {
        if (isset($metadata['contacts'])) {
            $this->validateContacts($metadata['contacts']);
        }
    }

    private function validateContacts(array $contacts)
    {
        $validContactTypes = array ("technical", "administrative", "support", "billing", "other");
        foreach ($contacts as $c) {
            if (!isset($c['contactType'])) {
                $this->logWarn("contactType not set");
                continue;
            }
            if (!in_array($c['contactType'], $validContactTypes)) {
                $this->logWarn("invalid contactType");
                continue;
            }
            if (isset($c['emailAddress']) && 0 === strlen($c['emailAddress'])) {
                if (false === filter_var($c['emailAddress'], FILTER_VALIDATE_EMAIL)) {
                    $this->logWarn("invalid emailAddress");
                    continue;
                }
            }
        }
    }
}
