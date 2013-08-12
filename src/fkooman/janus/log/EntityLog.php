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
        $this->l = array();
    }

    public function err($id, $message)
    {
        $this->logEntry($id, $message, EntityLog::ERR);
    }

    public function warn($id, $message)
    {
        $this->logEntry($id, $message, EntityLog::WARN);
    }

    public function logEntry($id, $message, $level)
    {
        if (!array_key_exists($id, $this->l)) {
            //$this->l[$id] = array();
            $this->l[$id]["messages"] = array();
        }
        array_push($this->l[$id]["messages"], array("level" => $level, "message" => $message));
    }

    public function toJson()
    {
        return json_encode($this->l);
    }

}
