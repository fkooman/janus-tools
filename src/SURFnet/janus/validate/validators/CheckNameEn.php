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

namespace SURFnet\janus\validate\validators;

use SURFnet\janus\validate\Validate;
use SURFnet\janus\validate\ValidateInterface;

class CheckNameEn extends Validate implements ValidateInterface
{

    public function sp(array $entityData, array $metadata, array $allowedEntities, array $blockedEntities, $arp)
    {
        if (!isset($metadata['name']['en'])) {
            $this->logWarn("no english name set");
        }
    }

    public function oauth(array $entityData, array $metadata, array $allowedEntities, array $blockedEntities, $arp)
    {
        if (!isset($metadata['name']['en'])) {
            $this->logWarn("no english name set");
        }
    }

    public function idp(array $entityData, array $metadata, array $allowedEntities, array $blockedEntities, array $disableConsent, array $entities)
    {
        if (!isset($metadata['name']['en'])) {
            $this->logWarn("no english name set");
        }
    }
}
