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
        foreach ($contacts as $k => $v) {
            if (!isset($v['contactType'])) {
                $this->logWarn(sprintf("contactType not set [%s]", $k));
                continue;
            }
            if (!in_array($v['contactType'], $validContactTypes)) {
                $this->logWarn(sprintf("invalid contactType [%s]", $k));
                continue;
            }
            if (isset($v['emailAddress']) && 0 === strlen($v['emailAddress'])) {
                if (false === filter_var($v['emailAddress'], FILTER_VALIDATE_EMAIL)) {
                    $this->logWarn(sprintf("invalid emailAddress [%s]", $k));
                    continue;
                }
            }
        }
    }
}
