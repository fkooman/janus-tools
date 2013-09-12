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

class CheckAcs extends Validate implements ValidateInterface
{

    public function sp(array $entityData, array $metadata, array $allowedEntities, array $blockedEntities, $arp)
    {
        if (!isset($metadata['AssertionConsumerService'])) {
            $this->logWarn("no AssertionConsumerService");

            return;
        }
        foreach ($metadata['AssertionConsumerService'] as $k => $v) {
            $this->validateEndpoint('AssertionConsumerService', $k, $v);
        }
    }

    private function validateEndpoint($type, $k, array $v)
    {
        if (!isset($v['Location'])) {
            $this->logWarn(sprintf("%s Location field missing [%s]", $type, $k));

            return;
        }
        if (false === filter_var($v['Location'], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            $this->logWarn(sprintf("%s invalid Location [%s]", $type, $k));

            return;
        }
        if (0 !== strpos($v['Location'], "https://")) {
            $this->logWarn(sprintf("%s non SSL Location specified [%s]", $type, $k));

            return;
        }

        if (!isset($v['Binding'])) {
            $this->logWarn(sprintf("%s Binding field missing [%s]", $type, $k));

            return;
        }
    }
}
