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

class CheckInactiveMetadataFields extends Validate implements ValidateInterface
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
        array $disableConsent,
        array $entities
    ) {
        $this->_checkInactiveMetadata($metadata, 'logo', 0, 'href');
        $this->_checkInactiveMetadata($metadata, 'base64attributes');
        $this->_checkInactiveMetadata($metadata, 'NameIDFormats', 0);
        $this->_checkInactiveMetadata($metadata, 'NameIDFormats', 1);
        $this->_checkInactiveMetadata($metadata, 'NameIDFormats', 2);
        $this->_checkInactiveMetadata($metadata, 'redirect.validate');
        $this->_checkInactiveMetadata($metadata, 'SingleLogoutService', 0, 'Binding');
        $this->_checkInactiveMetadata($metadata, 'SingleLogoutService', 0, 'Location');
        $this->_checkInactiveMetadata($metadata, 'url', 'en');
    }

    private function _checkInactiveMetadata(
        array $metadata,
        $key,
        $subKey = null,
        $subSubKey = null
    ) {
        if (!is_null($subSubKey)) {
            if (isset($metadata[$key][$subKey][$subSubKey])) {
                $this->logWarn('Contains Inactive metadatafield - ' . $key . ':' . $subKey . ':' . $subSubKey);
            }
        } elseif (!is_null($subKey)) {

            if (isset($metadata[$key][$subKey])) {
                $this->logWarn('Contains Inactive metadatafield - ' . $key . ':' . $subKey);
            }

        } else {
            if (isset($metadata[$key])) {
                $this->logWarn('Contains Inactive metadatafield - ' . $key);
            }
        }
    }
}