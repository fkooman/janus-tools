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

class SurfCheckAclContainsOnlyOwnStateSps extends Validate implements ValidateInterface
{

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
        array $disableConsent
    ) {
        $ownState = $entityData['state'];
        foreach ($allowedEntities as $k => $allowedEntity) {
            foreach ($this->entities as $e) {
                if ($e['entityData']['type'] == "saml20-sp") {
                    if ($e['entityData']['entityid'] == $allowedEntity) {
                        if ($e['entityData']['state'] !== $ownState) {
                            $this->logWarning(
                                "ACL contains entity which is not in " . $ownState . " state [" . $allowedEntity . "]"
                            );
                        }
                    }
                }
            }

        }

    }
}
