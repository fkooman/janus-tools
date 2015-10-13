<?php

/**
 * Copyright 2013 François Kooman <francois.kooman@surfnet.nl>
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

namespace SURFnet\janus\export;

use PDO;

class Export
{
    /** @var PDO */
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    private function validateType($type)
    {
        $validTypes = array(
            "saml20-idp",
            "saml20-sp"
        );

        if (!in_array($type, $validTypes)) {
            throw new ExportException("invalid type");
        }
    }

    private function validateState($state)
    {
        $validStates = array(
            "testaccepted",
            "prodaccepted"
        );

        if (!in_array($state, $validStates)) {
            throw new ExportException("invalid state");
        }
    }

    /**
     * Fetch all entities of a certain type with a state
     */
    public function getEntities($type, $state)
    {
        $this->validateType($type);
        $this->validateState($state);

        $sql = <<< EOF
SELECT
    CONNECTION.id, CONNECTION.revisionNr
FROM
    janus__connection AS CONNECTION
INNER JOIN
    janus__connectionRevision AS CONNECTION_REVISION ON
        CONNECTION_REVISION.eid = CONNECTION.id AND
        CONNECTION_REVISION.revisionid = CONNECTION.revisionNr
WHERE
    CONNECTION_REVISION.active = "yes" AND CONNECTION_REVISION.state=:state AND CONNECTION.type=:type
EOF;

        $sth = $this->db->prepare($sql);
        $sth->bindValue(":state", $state, PDO::PARAM_STR);
        $sth->bindValue(":type", $type, PDO::PARAM_STR);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        $entities = array();

        foreach ($result as $e) {
            $entities[] = $this->getEntity($type, $e['id'], $e['revisionNr']);
        }

        return $entities;
    }

    private function getAllowedEntities($eid, $revisionid)
    {
        $sql = <<< EOF
    SELECT
        ALLOWED_CONNECTION.name AS entityid
    FROM
        janus__connectionRevision AS CONNECTION_REVISION
    INNER JOIN
        `janus__allowedConnection` a ON
        a.connectionRevisionId = CONNECTION_REVISION.id
    INNER JOIN
        janus__connection AS ALLOWED_CONNECTION ON
        ALLOWED_CONNECTION.id = a.remoteeid
    WHERE
        CONNECTION_REVISION.eid = :eid AND
        CONNECTION_REVISION.revisionid = :revisionid
EOF;

        $sth = $this->db->prepare($sql);
        $sth->bindValue(":eid", $eid, PDO::PARAM_INT);
        $sth->bindValue(":revisionid", $revisionid, PDO::PARAM_INT);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_COLUMN);

        return $result;
    }

    private function getBlockedEntities($eid, $revisionid)
    {
        $sql = <<< EOF
    SELECT
        BLOCKED_CONNECTION.name AS entityid
    FROM
        janus__connectionRevision AS CONNECTION_REVISION
    INNER JOIN
        `janus__blockedConnection` b ON
        b.connectionRevisionId = CONNECTION_REVISION.id
    INNER JOIN
        janus__connection AS BLOCKED_CONNECTION ON
        BLOCKED_CONNECTION.id = b.remoteeid
    WHERE
        CONNECTION_REVISION.eid = :eid AND
        CONNECTION_REVISION.revisionid = :revisionid
EOF;

        $sth = $this->db->prepare($sql);
        $sth->bindValue(":eid", $eid, PDO::PARAM_INT);
        $sth->bindValue(":revisionid", $revisionid, PDO::PARAM_INT);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_COLUMN);

        return $result;
    }

    public function getEntity($type, $eid, $revisionid)
    {
        $sql = <<< EOF
SELECT
    *
FROM
    janus__connectionRevision
WHERE
    eid = :eid AND revisionid = :revisionid
EOF;
        $sth = $this->db->prepare($sql);
        $sth->bindValue(":eid", $eid);
        $sth->bindValue(":revisionid", $revisionid);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        // there should only be one entity
        if (1 !== count($result)) {
            throw new ExportException("duplicate eid,revisionid entry");
        }

        $entity = array();

        $entity['entityData'] = $result[0];

        // metadata
        $entity['metadata'] = $this->getMetadata($eid, $revisionid);

        // allowedEntities
        $entity['allowedEntities'] = $this->getAllowedEntities($eid, $revisionid);

        // blockedEntities
        $entity['blockedEntities'] = $this->getBlockedEntities($eid, $revisionid);

        // disableConsent (IdP only)
        if ("saml20-idp" === $type) {
            $entity['disableConsent'] = $this->getDisableConsent($eid, $revisionid);
        }

        /**
         * arp (SP only)
         *
         * Attributes are store like:
         * array()            --> no attributes are released
         * array("x","y","z") --> attributes x, y, z are released
         * false              --> no ARP (so *ALL* attributes are released)
         */
        if ("saml20-sp" === $type) {
            $entity['attributes'] = unserialize($entity['entityData']['arp_attributes']);
            unset($entity['entityData']['arp_attributes']);
        }

        return $entity;
    }

    private function getMetadata($eid, $revisionid)
    {
        $sql = <<< EOF
    SELECT
        METADATA.`key`, METADATA.`value`
    FROM
        janus__connectionRevision AS CONNECTION_REVISION
    INNER JOIN
        janus__metadata AS METADATA ON
        METADATA.connectionRevisionId = CONNECTION_REVISION.id
    WHERE
        CONNECTION_REVISION.eid = :eid AND
        CONNECTION_REVISION.revisionid = :revisionid
EOF;

        $sth = $this->db->prepare($sql);
        $sth->bindValue(":eid", $eid, PDO::PARAM_INT);
        $sth->bindValue(":revisionid", $revisionid, PDO::PARAM_INT);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        $metadata = array();
        foreach ($result as $kv) {
            $metadata[$kv['key']] = $kv['value'];
        }

        return $this->metadataToArray($metadata);
    }

    /**
     * Returns ? IdP only?!
     *
     * @return array() -> entityids of the entities for which consent is
     *                    disabled, *NO* eid?!
     */
    private function getDisableConsent($eid, $revisionid)
    {
        $sql = <<< EOF
    SELECT
        BLOCKED_CONNECTION.name AS entityid
    FROM
        janus__connectionRevision AS CONNECTION_REVISION
    INNER JOIN
        `janus__disableConsent` dc ON
        dc.connectionRevisionId = CONNECTION_REVISION.id
    INNER JOIN
        janus__connection AS BLOCKED_CONNECTION ON
        BLOCKED_CONNECTION.id = dc.remoteeid
    WHERE
        CONNECTION_REVISION.eid = :eid AND
        CONNECTION_REVISION.revisionid = :revisionid
EOF;
        $sth = $this->db->prepare($sql);
        $sth->bindValue(":eid", $eid);
        $sth->bindValue(":revisionid", $revisionid);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_COLUMN);

        return $result;
    }

    // FIXME: this method should be much simpler and less ugly and support
    // unlimited depths
    private function metadataToArray(array $metadata)
    {
        foreach ($metadata as $k => $v) {
            // if k contain as colon there may be multiple values underneath
            if (empty($v)) {
                unset($metadata[$k]);
            } else {
                if (false !== strpos($k, ":")) {
                    $e = explode(":", $k);
                    if (2 === count($e)) {
                        // only simple case for now
                        $metadata[$e[0]][$e[1]] = $v;
                        unset($metadata[$k]);
                    } elseif (3 === count($e)) {
                        $metadata[$e[0]][$e[1]][$e[2]] = $v;
                        unset($metadata[$k]);
                    } elseif (4 === count($e)) {
                        $metadata[$e[0]][$e[1]][$e[2]][$e[3]] = $v;
                        unset($metadata[$k]);
                    } else {
                        throw new ExportException("unsupported array depth in metadata");
                    }
                }
            }
        }

        return $metadata;
    }
}
