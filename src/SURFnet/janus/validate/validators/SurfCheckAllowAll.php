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

class SurfCheckAllowAll extends Validate implements ValidateInterface
{

    public function sp($entityData, $metadata, $allowedEntities, $blockedEntities, $arp)
    {
        if ("no" === $entityData['allowedall']) {
            $this->logWarn("sp must have 'allowedall' set");
        }
    }

    public function idp($entityData, $metadata, $allowedEntities, $blockedEntities, $disableConsent)
    {
        if ("yes" === $entityData['allowedall']) {
            $this->logWarn("idp must not have 'allowedall' set");
        }
    }
}