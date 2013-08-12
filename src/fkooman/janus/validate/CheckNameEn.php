<?php

namespace fkooman\janus\validate;

class CheckNameEn extends Validate implements ValidateInterface
{
    public function validateEntities()
    {
        foreach ($this->entities as $e) {
            if (!isset($e['metadata']['name']['en'])) {
                $this->logWarn($e, "no english name set");
            }
        }
    }
}
