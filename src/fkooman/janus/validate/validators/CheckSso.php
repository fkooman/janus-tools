<?php

namespace fkooman\janus\validate\validators;

use fkooman\janus\validate\Validate;
use fkooman\janus\validate\ValidateInterface;

class CheckSso extends Validate implements ValidateInterface
{

    public function idp($entityData, $metadata, $allowedEntities, $blockedEntities, $disableConsent)
    {
        if (!isset($metadata['SingleSignOnService'])) {
            $this->logWarn("no SingleSignOnService");

            return;
        }
        foreach ($metadata['SingleSignOnService'] as $k => $v) {
            $this->validateEndpoint('SingleSignOnService', $k, $v);
        }
    }

    public function sp($entityData, $metadata, $allowedEntities, $blockedEntities, $arp)
    {
    }

    private function validateEndpoint($type, $k, array $v)
    {
        if (!isset($v['Location'])) {
            $this->logWarn(sprintf("%s Location field missing [%s]", $type, $k));

            return;
        }
        if (false === filter_var($v['Location'], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            $this->logWarn(sprintf("%s invalid Location [%s]", $type, $k));

            return;
        }
        if (0 !== strpos($v['Location'], "https://")) {
            $this->logWarn(sprintf("%s non SSL Location specified [%s]", $type, $k));

            return;
        }

        if (!isset($v['Binding'])) {
            $this->logWarn(sprintf("%s Binding field missing [%s]", $type, $k));

            return;
        }
    }
}
