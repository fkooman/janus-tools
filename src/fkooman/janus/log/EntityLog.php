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

    public function err($type, $id, $module, $message)
    {
        $this->logEntry($type, $id, $module, $message, EntityLog::ERR);
    }

    public function warn($type, $id, $module, $message)
    {
        $this->logEntry($type, $id, $module, $message, EntityLog::WARN);
    }

    public function logEntry($type, $id, $module, $message, $level)
    {
        if (!array_key_exists($id, $this->l[$type])) {
            $this->l[$type][$id]["messages"] = array();
        }
        array_push($this->l[$type][$id]["messages"], array("module" => $module, "level" => $level, "message" => $message));
    }

    public function toJson()
    {
        return json_encode($this->l);
    }

}
