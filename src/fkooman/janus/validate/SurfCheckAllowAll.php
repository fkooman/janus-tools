<?php

namespace fkooman\janus\validate;

class SurfCheckAllowAll extends Validate implements ValidateInterface
{
    public function validateEntities()
    {
        foreach ($this->entities as $e) {
            if ("saml20-sp" === $e['entityData']['type']) {
                if ("no" === $e['entityData']['allowedall']) {
                    $this->logWarn($e, "sp must have 'allowedall' set");
                }
            }

            if ("saml20-idp" === $e['entityData']['type']) {
                if ("yes" === $e['entityData']['allowedall']) {
                    $this->logWarn($e, "idp must not have 'allowedall' set");
                }
            }
        }
    }
}
