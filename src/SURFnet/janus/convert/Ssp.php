<?php

namespace SURFnet\janus\convert;

/**
 * Convert the export and ACL to a simpleSAMLphp configuration file (as JSON)
 */
class Ssp
{
    /** @var array */
    private $entities;

    /** @var array */
    private $acl;

    /** @var array */
    private $idp;

    /** @var array */
    private $sp;

    public function __construct(array $entities, array $acl)
    {
        $this->entities = $entities;
        $this->acl = $acl;

        $this->idp = array();
        $this->sp = array();

        $this->moveEntityData();
        $this->moveMetadata();

        $this->segregateEntities();

        $this->addAcl();
        $this->addArp();
        $this->fixDisableConsentIdp();
        $this->fixDisableConsentSp();

        //$this->convertToUIInfo();
    }

    /**
     * Move the IdPs to the IdP array and SPs to the SP array
     */
    private function segregateEntities()
    {
        foreach ($this->entities as $entity) {
            if ("saml20-idp" === $entity['type']) {
                $this->idp[] = $entity;
            }
            if ("saml20-sp" === $entity['type']) {
                $this->sp[] = $entity;
            }
        }
    }

    private function moveEntityData()
    {
        foreach ($this->entities as $index => $entity) {
            foreach ($entity['entityData'] as $k => $v) {
                if (!array_key_exists($k, $entity)) {
                    $entity[$k] = $v;
                }
            }
            unset($entity['entityData']);
            $this->entities[$index] = $entity;
        }
    }

    private function moveMetadata()
    {
        foreach ($this->entities as $index => $entity) {
            foreach ($entity['metadata'] as $k => $v) {
                if (!array_key_exists($k, $entity)) {
                    $entity[$k] = $v;
                }
            }
            unset($entity['metadata']);
            $this->entities[$index] = $entity;
        }
    }

    private function addAcl()
    {
        foreach ($this->sp as $index => $entity) {
            $entityId = $entity['entityid'];
            $entity['IDPList'] = $this->acl['saml20-sp'][$entityId];
            $this->sp[$index] = $entity;
        }
    }

    private function addArp()
    {
        foreach ($this->sp as $index => $entity) {
            if (array_key_exists('attributes', $entity) && is_array($entity['attributes'])) {
                $entity['attributes'] = array_keys($entity['attributes']);
            } else {
                $entity['attributes'] = array();
            }
            $this->sp[$index] = $entity;
        }
    }

    private function fixDisableConsentIdP()
    {
        foreach ($this->idp as $index => $entity) {
            if (array_key_exists('disableConsent', $entity)) {
                $entity['consent.disable'] = $entity['disableConsent'];
            }
            $this->idp[$index] = $entity;
        }
    }

    private function fixDisableConsentSp()
    {
        foreach ($this->sp as $index => $entity) {
            if (isset($entity['coin']['no_consent_required'])) {
                $entity['consent.disable'] = (1 == $entity['coin']['no_consent_required'] ? true : false);
            } else {
                $entity['consent.disable'] = false;
            }
            $this->sp[$index] = $entity;
        }
    }

    private function convertToUIInfo()
    {
        // some keys belong in UIInfo (under a different name)
        foreach ($entities as $eid => $metadata) {
            $uiInfo = array();
            $discoHints = array();

            if (array_key_exists("displayName", $metadata)) {
                $uiInfo['DisplayName'] = $metadata['displayName'];
                unset($entities[$eid]['displayName']);
            }
            if (array_key_exists("keywords", $metadata)) {
                foreach ($metadata['keywords'] as $language => $keywords) {
                    $filteredKeywords = filterKeywords($keywords);
                    if (0 !== count($filteredKeywords)) {
                        $uiInfo['Keywords'][$language] = $filteredKeywords;
                    }
                }
                unset($entities[$eid]['keywords']);
            }
            if (array_key_exists("geoLocation", $metadata)) {
                $geo = validateGeo($metadata['geoLocation']);
                if (FALSE !== count($geo)) {
                    $discoHints['GeolocationHint'] = array($geo);
                } else {
                    _l($metadata, "WARNING", "invalid GeolocationHint");
                }
                unset($entities[$eid]['geoLocation']);
            }
            if (array_key_exists("logo", $metadata)) {
                $errorMessage = array();
                $logo = validateLogo($metadata["logo"][0], $errorMessage);
                if (FALSE !== $logo) {
                    $uiInfo['Logo'] = array($logo);
                } else {
                    _l($metadata, "WARNING", "invalid Logo configuration (" . implode(", ", $errorMessage) . ")");
                }
                unset($entities[$eid]['logo']);
            }
            if (0 !== count($uiInfo)) {
                $entities[$eid]['UIInfo'] = $uiInfo;
            }
            if (0 !== count($discoHints)) {
                $entities[$eid]['DiscoHints'] = $discoHints;
            }
        }
    }

    public function getIdps()
    {
        return array_values($this->idp);
    }

    public function getSps()
    {
        return array_values($this->sp);
    }
}
