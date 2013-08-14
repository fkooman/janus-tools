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

class SurfCheckDefaultSP extends Validate implements ValidateInterface
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
        if (!empty($metadata["coin"]["institution_id"])) {
            $requiredSurfnetSpsPerStatus = $this->config->s("require_surfnet:" . $entityData['state'])->toArray();
            $this->checkRequiredSps($requiredSurfnetSpsPerStatus, $allowedEntities, $blockedEntities);

            $disallowedSurfnetSpsPerStatus = $this->config->s("disallowed_surfnet:" . $entityData['state'])->toArray();
            $this->checkDisallowdSps($disallowedSurfnetSpsPerStatus, $allowedEntities);
        } else {
            $requiredNonSurfnetSpsPerStatus = $this->config->s("require_nonsurfnet:" . $entityData['state'])->toArray();
            $this->checkRequiredSps($requiredNonSurfnetSpsPerStatus, $allowedEntities, $blockedEntities);

            $disallowedNonSurfnetSpsPerStatus = $this->config->s("disallowed_nonsurfnet:" . $entityData['state'])->toArray();
            $this->checkDisallowdSps($disallowedNonSurfnetSpsPerStatus, $allowedEntities);
        }
    }

    /**
     * @param array $requiredSpsPerStatus
     * @param array $allowedEntities
     * @param array $blockedEntities
     */
    private function checkRequiredSps(array $requiredSpsPerStatus, array $allowedEntities, array $blockedEntities)
    {
        foreach ($requiredSpsPerStatus as $rSP) {
            if (!in_array($rSP, $allowedEntities)) {
                $this->logWarn(sprintf("Required SP is not allowed (ACL): %s", $rSP));
            }
            if (in_array($rSP, $blockedEntities)) {
                $this->logWarn(sprintf("Required SP is blocked (ACL): %s", $rSP));
            }
        }
    }

    /**
     * @param array $disallowedSpsPerStatus
     * @param array $allowedEntities
     */
    private function checkDisallowdSps(array $disallowedSpsPerStatus, array $allowedEntities)
    {
        foreach ($disallowedSpsPerStatus as $dSP) {
            if (in_array($dSP, $allowedEntities)) {
                $this->logWarn(sprintf("Disallowed SP is allowed (ACL): %s", $dSP));
            }
        }
    }
}
