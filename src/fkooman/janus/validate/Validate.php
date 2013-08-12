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
            $this->currentEntity = $e;
            switch ($e['entityData']['type']) {
                case "saml20-sp":
                    $this->sp($e['entityData'], $e['metadata'], $e['allowedEntities'], $e['blockedEntities'], $e['arp']);
                    break;
                case "saml20-idp":
                    $this->idp($e['entityData'], $e['metadata'], $e['allowedEntities'], $e['blockedEntities'], $e['disableConsent']);
                    break;
                default:
                    throw new Exception("unsupported entity type");
            }
        }
    }

    public function logWarn($message)
    {
        $this->log->warn($this->currentEntity['entityData']['type'], $this->currentEntity['entityData']['entityid'], get_class($this), $message);
    }

    public function logErr($message)
    {
        $this->log->err($this->currentEntity['entityData']['type'], $this->currentEntity['entityData']['entityid'], get_class($this), $message);
    }

}
