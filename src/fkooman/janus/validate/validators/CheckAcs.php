<?php

namespace fkooman\janus\validate\validators;

use fkooman\janus\validate\Validate;
use fkooman\janus\validate\ValidateInterface;

class CheckAcs extends Validate implements ValidateInterface
{

    public function idp($entityData, $metadata, $allowedEntities, $blockedEntities, $disableConsent)
    {
    }

    public function sp($entityData, $metadata, $allowedEntities, $blockedEntities, $arp)
    {

        if (!isset($metadata['AssertionConsumerService'])) {
            $this->logWarn("no AssertionConsumerService set");

            return;
        }
        foreach ($metadata['AssertionConsumerService'] as $k => $v) {
            $this->validateEndpoint($k, $v);
        }

    }

    private function validateEndpoint($k, array $v)
    {
        if (!isset($v['Location'])) {
            $this->logWarn(sprintf("Location field missing [%s]", $k));

            return;
        }
        if (false === filter_var($v['Location'], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            $this->logWarn(sprintf("invalid Location [%s]", $k));

            return;
        }
        if (0 !== strpos($v['Location'], "https://")) {
            $this->logWarn(sprintf("non SSL Location specified [%s]", $k));

            return;
        }

        if (!isset($v['Binding'])) {
            $this->logWarn(sprintf("Binding field missing [%s]", $k));

            return;
        }
    }
}
