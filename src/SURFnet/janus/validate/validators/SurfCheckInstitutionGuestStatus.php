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

class SurfCheckInstitutionGuestStatus  extends Validate implements ValidateInterface
{
    /**
     * @param array $entityData
     * @param array $metadata
     * @param array $allowedEntities
     * @param array $blockedEntities
     * @param array $disableConsent
     * @param array $entities
     */
    public function idp(
        array $entityData,
        array $metadata,
        array $allowedEntities,
        array $blockedEntities,
        array $disableConsent,
        array $entities
    ) {

        if (!empty($metadata['coin']['institution_id'])) {
            if ('None' !== $metadata['coin']['guest_qualifier']) {
                $this->logWarn('guest_qualifier is NOT "None"');
            }
        }
    }
}