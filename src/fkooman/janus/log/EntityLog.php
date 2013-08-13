<?php

namespace fkooman\janus\log;

class EntityLog
{
    const WARN = 10;
    const ERR  = 20;

    /** @var array */
    private $l;

    public function __construct()
    {
        $this->l = array(
            "saml20-idp" => array(),
            "saml20-sp" => array()
        );
    }

    public function err(array $entity, $module, $message)
    {
        $this->logEntry($entity, $module, $message, EntityLog::ERR);
    }

    public function warn(array $entity, $module, $message)
    {
        $this->logEntry($entity, $module, $message, EntityLog::WARN);
    }

    public function logEntry(array $entity, $module, $message, $level)
    {
        $eid = $entity['entityData']['eid'];
        $entityId = $entity['entityData']['entityid'];
        $type = $entity['entityData']['type'];
        $name = isset($entity['metadata']['name']['en']) ? $entity['metadata']['name']['en'] : $entityId;

        if (!array_key_exists($entityId, $this->l[$type])) {
            $this->l[$type][$entityId] = array(
                "name" => $name,
                "eid" => $eid,
                "messages" => array()
            );
        }
        array_push($this->l[$type][$entityId]["messages"], array("module" => $module, "level" => $level, "message" => $message));
    }

    public function toJson()
    {
        return json_encode($this->l);
    }

}
