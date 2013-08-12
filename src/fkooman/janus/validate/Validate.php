<?php

namespace fkooman\janus\validate;

use fkooman\janus\log\EntityLog;

abstract class Validate implements ValidateInterface
{
    const WARNING = 10;
    const ERROR   = 20;

    private $log;
    protected $entities;

    public function __construct(array $entities, EntityLog $log)
    {
        $this->log = $log;
        $this->entities = $entities;
    }

    public function logWarn(array $e, $message)
    {
        $this->log->warn($e['entityData']['entityid'], $message);
    }

    public function logErr(array $e, $message)
    {
        $this->log->err($e['entityData']['entityid'], $message);
    }

}
