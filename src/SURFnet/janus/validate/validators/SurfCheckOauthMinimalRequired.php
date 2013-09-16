<?php
/**
 * Copyright 2013 Remold Krol <remold.krol@everett.nl>
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

class SurfCheckOauthMinimalRequired extends Validate implements ValidateInterface
{
    public function sp(
        array $entityData,
        array $metadata,
        array $allowedEntities,
        array $blockedEntities,
        $arp
    ) {

    }

    public function idp(
        array $entityData,
        array $metadata,
        array $allowedEntities,
        array $blockedEntities,
        array $disableConsent
    ) {

    }

    /**
     * @param array $entityData
     * @param array $metadata
     * @param array $allowedEntities
     * @param array $blockedEntities
     * @param $arp
     */
    public function oauth(
        array $entityData,
        array $metadata,
        array $allowedEntities,
        array $blockedEntities,
        $arp
    ) {
        if (isset($metadata['coin'])) {
            if (isset($metadata['coin']['oauth'])) {
                // $this->checkKey($metadata['coin']['oauth']);
            } else {
                $this->logErr('No oauth metadata"');
            }
        } else {
            $this->logErr('No coin metadata"');
        }

    }

    /**
     * @param array $oauth
     */
    private function checkKey(array $oauth)
    {
        if (isset($oauth['consumer_key'])) {
            if (!$this->isRegex('consumer_key')) {
                $this->logWarn("Consumer Key is not a regular expression");
            }

        } else {
            $this->logErr('No consumer key');
        }
    }

    /**
     * @param string $strToCheck
     */
    private function isRegex($strToCheck)
    {
        $trackErrors = ini_set('track_errors', 'on');
        $php_errormsg = '';
        @preg_match($strToCheck, '');
        if ($php_errormsg) {
            $isRegex = false;
        } else {
            $isRegex = true;
        }
        if ($trackErrors !== false) {
            ini_set('track_errors', $trackErrors);
        }

        return $isRegex;
    }
}
