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

namespace SURFnet\janus\validate;

use SURFnet\janus\log\EntityLog;
use fkooman\Config\Config;

abstract class Validate implements ValidateInterface
{
    /** @var \fkooman\janus\log\EntityLog */
    private $log;

    /** @var array */
    private $entities;

    /** @var \fkooman\Config\Config */
    protected $globalConfig;

    /** @var \fkooman\Config\Config */
    protected $config;

    /** @var array */
    private $currentEntity;

    /** @var string */
    private $validatorName;

    public function __construct(array $entities, Config $config, EntityLog $log)
    {
        $this->log = $log;
        $this->globalConfig = $config;

        $this->validatorName = substr(get_class($this), strrpos(get_class($this), '\\') + 1);
        $this->config = $this->globalConfig->s($this->validatorName);

        $this->entities = $entities;
        $this->currentEntity = null;
    }

    public function validateEntities()
    {
        foreach ($this->entities as $e) {
            $this->currentEntity = $e;
            switch ($e['entityData']['type']) {
                case "saml20-sp":
                    // check if entityid should be ignored
                    if (!in_array($e['entityData']['entityid'], $this->config->s('ignoreSp')->toArray())) {
                        $this->sp(
                            $e['entityData'],
                            $e['metadata'],
                            $e['allowedEntities'],
                            $e['blockedEntities'],
                            $e['arp']
                        );
                    }
                    break;
                case "saml20-idp":
                    if (!in_array($e['entityData']['entityid'], $this->config->s('ignoreIdp')->toArray())) {
                        $this->idp(
                            $e['entityData'],
                            $e['metadata'],
                            $e['allowedEntities'],
                            $e['blockedEntities'],
                            $e['disableConsent']
                        );
                    }
                    break;
                default:
                    throw new Exception("unsupported entity type");
            }
        }
    }

    public function logWarning($message)
    {
        $this->log->warning($this->currentEntity, $this->validatorName, $message);
    }

    public function logError($message)
    {
        $this->log->error($this->currentEntity, $this->validatorName, $message);
    }

    public function logFatal($message)
    {
        $this->log->fatal($this->currentEntity, $this->validatorName, $message);
    }
}
