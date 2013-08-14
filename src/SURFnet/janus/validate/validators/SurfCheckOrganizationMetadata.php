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

class SurfCheckOrganizationMetadata extends Validate implements ValidateInterface
{
    /**
     * @param array $entityData
     * @param array $metadata
     * @param array $allowedEntities
     * @param array $blockedEntities
     * @param $arp
     */
    public function sp(array $entityData, array $metadata, array $allowedEntities, array $blockedEntities, $arp)
    {
        $this->checkOrganizationMetadata($metadata, 'OrganizationDisplayName');
        $this->checkOrganizationMetadata($metadata, 'OrganizationName');
        $this->checkOrganizationURL($metadata);
    }

    /**
     * @param array $entityData
     * @param array $metadata
     * @param array $allowedEntities
     * @param array $blockedEntities
     * @param array $disableConsent
     */
    public function idp(array $entityData, array $metadata, array $allowedEntities, array $blockedEntities, array $disableConsent)
    {
        $this->checkOrganizationMetadata($metadata, 'OrganizationDisplayName');
        $this->checkOrganizationMetadata($metadata, 'OrganizationName');
        $this->checkOrganizationURL($metadata);
    }

    /**
     * @param array $metadata
     * @param $keyToCheck
     */
    private function checkOrganizationMetadata(array $metadata, $keyToCheck)
    {
        if (!isset($metadata[$keyToCheck])) {
            $this->logWarn("no " . $keyToCheck);
            return;
        }
    }

    /**
     * @param array $metadata
     */
    private function checkOrganizationURL(array $metadata)
    {
        if (!isset($metadata['OrganizationURL'])) {
            $this->logWarn("no OrganizationURL");
            return;
        }
        foreach ($metadata['OrganizationURL'] as $k => $v)
            if (filter_var($v, FILTER_VALIDATE_URL) === FALSE) {
                $this->logWarn("OrganizationURL:" . $k . ' is not a valid URL');
            }
    }
}