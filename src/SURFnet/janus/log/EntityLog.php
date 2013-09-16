<?php

/**
 * Copyright 2013 François Kooman <francois.kooman@surfnet.nl>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace SURFnet\janus\log;

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
        $state = $entity['entityData']['state'];
        $name = isset($entity['metadata']['name']['en']) ? $entity['metadata']['name']['en'] : $entityId;

        if (!array_key_exists($entityId, $this->l[$type])) {
            $this->l[$type][$entityId] = array(
                "name" => $name,
                "eid" => $eid,
                "state" => $state,
                "messages" => array()
            );
        }
        array_push(
            $this->l[$type][$entityId]["messages"],
            array(
                "module" => $module,
                "level" => $level,
                "message" => $message
            )
        );
    }

    public function toJson()
    {
        $this->l['generatedAt'] = date("r", time());

        return json_encode($this->l);
    }
}
