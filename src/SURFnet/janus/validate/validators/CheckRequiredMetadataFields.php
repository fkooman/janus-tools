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

class CheckRequiredMetadataFields extends Validate implements ValidateInterface
{


    /**
     * @param array $entityData
     * @param array $metadata
     * @param array $allowedEntities
     * @param array $blockedEntities
     * @param $arp
     */
    public function sp(
        array $entityData,
        array $metadata,
        array $allowedEntities,
        array $blockedEntities,
        $arp
    ) {
        $requiredSpFields = $this->config->s("requiredSpField")->toArray();
        foreach ($requiredSpFields as $reqField) {
            $this->_checkRequiredField($metadata, $reqField);
        }
        $requiredSpLangFields = $this->config->s("requiredSpLangField")->toArray();
        foreach ($requiredSpLangFields as $reqLangField) {
            $this->_checkRequiredLangField($metadata, $reqLangField);
        }
        $requiredSpLangUrls = $this->config->s("requiredSpLangUrl")->toArray();
        foreach ($requiredSpLangUrls as $reqLangUrl) {
            $this->_checkRequiredLangUrl($metadata, $reqLangUrl);
        }
    }

    /**
     * @param array $entityData
     * @param array $metadata
     * @param array $allowedEntities
     * @param array $blockedEntities
     * @param array $disableConsent
     */
    public function idp(
        array $entityData,
        array $metadata,
        array $allowedEntities,
        array $blockedEntities,
        array $disableConsent
    ) {
        $requiredIdpFields = $this->config->s("requiredIdpField")->toArray();
        foreach ($requiredIdpFields as $reqField) {
            $this->_checkRequiredField($metadata, $reqField);
        }
        $requiredIdpLangFields = $this->config->s("requiredIdpLangField")->toArray();
        foreach ($requiredIdpLangFields as $reqLangField) {
            $this->_checkRequiredLangField($metadata, $reqLangField);
        }
        $requiredIdpLangUrls = $this->config->s("requiredIdpLangUrl")->toArray();
        foreach ($requiredIdpLangUrls as $reqLangUrl) {
            $this->_checkRequiredLangUrl($metadata, $reqLangUrl);
        }
    }

    /**
     * @param array $metadata
     * @param string $keyToCheck
     */
    private function _checkRequiredLangField(array $metadata, $keyToCheck)
    {
        if (!$this->_checkRequiredField($metadata, $keyToCheck)) {
            return false;
        }
        $languages = $this->config->s("language")->toArray();
        foreach ($languages as $lang) {
            if (!isset($metadata[$keyToCheck][$lang])) {
                $this->logWarn("no " . $keyToCheck . '[' . $lang . ']');

            }
        }
        return true;
    }


    /**
     * @param array $metadata
     * @param string $keyToCheck
     */
    private function _checkRequiredLangUrl(array $metadata, $keyToCheck)
    {
        if (!$this->_checkRequiredLangField($metadata, $keyToCheck)) {
            return false;
        }
        foreach ($metadata[$keyToCheck] as $language => $url) {
            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
//                echo "url=".$url . PHP_EOL;
                $this->logWarn($keyToCheck . ':' . $language . ' is not a valid URL');
            }
        }
    }

    /**
     * @param array $metadata
     * @param string $keyToCheck
     */
    private function _checkRequiredField(array $metadata, $keyToCheck)
    {
        if (!isset($metadata[$keyToCheck])) {
            $this->logWarn("no " . $keyToCheck);

            return false;
        }
        return true;
    }

}
