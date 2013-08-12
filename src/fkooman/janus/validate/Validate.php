<?php

namespace fkooman\janus\validate;

use fkooman\janus\log\EntityLog;

abstract class Validate implements ValidateInterface
{
    private $log;
    private $entities;
    private $currentEntity;

    public function __construct(array $entities, EntityLog $log)
    {
        $this->log = $log;
        $this->entities = $entities;
        $this->currentEntity = null;
    }

    public function validateEntities()
    {
        foreach ($this->entities as $e) {
            $this->currentEntityId = $e['entityData']['entityid'];
            if ("saml20-sp" === $e['entityData']['type']) {
                $this->sp($e['entityData'], $e['metadata'], $e['allowedEntities'], $e['blockedEntities'], $e['arp']);
            } else {
                $this->idp($e['entityData'], $e['metadata'], $e['allowedEntities'], $e['blockedEntities'], $e['disableConsent']);
            }
        }
    }

    public function logWarn($message)
    {
        $this->log->warn($this->currentEntityId, $message);
    }

    public function logErr($message)
    {
        $this->log->err($this->currentEntityId, $message);
    }

}
