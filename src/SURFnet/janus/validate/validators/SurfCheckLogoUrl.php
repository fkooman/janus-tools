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

class SurfCheckLogoUrl extends Validate implements ValidateInterface
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
        $this->validateLogoURL($metadata);
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
        $this->validateLogoURL($metadata);
    }

    /**
     * @param array $metadata
     */
    private function validateLogoURL(
        array $metadata
    ) {
        if (isset($metadata['logo'])) {
            if (isset($metadata['logo'][0])) {
                if (isset($metadata['logo'][0]['url'])) {
                    $url = $metadata['logo'][0]['url'];
                    if ('https://.png' == $url) {
                        $this->logWarning('Logo URL contains default URL "https://.png"');

                        return;
                    }
                    if (false === filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
                        $this->logWarning(sprintf("Logo URL invalid Location [%s]", $url));

                        return;
                    }
                    if (0 !== strpos($url, "https://")) {
                        $this->logWarning(sprintf("Logo URL non SSL specified [%s]", $url));

                        return;
                    }
                    if (0 !== strpos($url, "https://static.surfconext.nl/media/")) {
                        $this->logWarning(sprintf("Logo not located on static.surfconext.nl/media [%s]", $url));

                        return;
                    }
                }
            }
        }
    }
}
