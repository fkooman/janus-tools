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

        $this->convertToUIInfo();

        $this->segregateEntities();

        $this->addAcl();
        $this->addArp();
        $this->fixDisableConsentIdp();
        $this->fixDisableConsentSp();

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
            if (isset($this->acl['saml20-sp'][$entityId])) {
                $entity['IDPList'] = $this->acl['saml20-sp'][$entityId];
                $this->sp[$index] = $entity;
            } 
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
        foreach ($this->entities as $index => $entity) {
            $uiInfo = array();
            $discoHints = array();

            // displayname
            if (array_key_exists("displayName", $entity)) {
                $uiInfo['DisplayName'] = $entity['displayName'];
                unset($this->entities[$index]['displayName']);
            }

            // keywords
            if (array_key_exists("keywords", $entity)) {
                foreach ($entity['keywords'] as $language => $keywords) {
                    $filteredKeywords = $this->filterKeywords($keywords);
                    if (0 !== count($filteredKeywords)) {
                        $uiInfo['Keywords'][$language] = $filteredKeywords;
                    }
                }
                unset($this->entities[$index]['keywords']);
            }

            // geo location
            if (array_key_exists("geoLocation", $entity)) {
                $geo = $this->validateGeo($entity['geoLocation']);
                if (FALSE !== count($geo)) {
                    $discoHints['GeolocationHint'] = array($geo);
                }
                unset($this->entities[$index]['geoLocation']);
            }

            // logo
            if (array_key_exists("logo", $entity)) {

                $url = isset($entity['logo'][0]['url']) ? $entity['logo'][0]['url'] : null;
                $width = isset($entity['logo'][0]['width']) ? intval($entity['logo'][0]['width']) : null;
                $height = isset($entity['logo'][0]['height']) ? intval($entity['logo'][0]['height']) : null;

                $logo = array();
                if (null !== $url) {
                    $logo['url'] = $url;
                }
                if (null !== $width) {
                    $logo['width'] = $width;
                }
                if (null !== $height) {
                    $logo['height'] = $height;
                }
                $uiInfo['Logo'] = array($logo);
                unset($this->entities[$index]['logo']);
            }
            if (0 !== count($uiInfo)) {
                $this->entities[$index]['UIInfo'] = $uiInfo;
            }
            if (0 !== count($discoHints)) {
                $this->entities[$index]['DiscoHints'] = $discoHints;
            }
        }
    }

    private function validateGeo($geoHints)
    {
        if (!empty($geoHints)) {
            $e = explode(",", $geoHints);
            if (2 !== count($e) && 3 !== count($e)) {
                return false;
            }
            if (2 === count($e)) {
                list($lat, $lon) = $e;
                $lat = trim($lat);
                $lon = trim($lon);

                return "geo:$lat,$lon";
            }
            if (3 === count($e)) {
                list($lat, $lon, $alt) = $e;
                $lat = trim($lat);
                $lon = trim($lon);
                $alt = trim($alt);

                return "geo:$lat,$lon,$alt";
            }
        }
    }

    private function filterKeywords($keywords)
    {
        $keywordsArray = explode(" ", $keywords);
        foreach ($keywordsArray as $k) {
            $keywordsArray = array_filter($keywordsArray, function ($v) {
                if (empty($v)) {
                    return false;
                }
                if (false !== strpos($v, "+")) {
                    return false;
                }
                if ($v !== htmlentities($v)) {
                    return false;
                }

                return true;
            });
        }
        sort($keywordsArray);

        return array_values(array_unique($keywordsArray));
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
