<?php


namespace Mapbender\LDAPBundle\Security\Provider;


use FOM\UserBundle\Component\Ldap\Client;
use Mapbender\LDAPBundle\Security\User\LDAPGroup;

class LDAPGroupProvider
{
    /** @var Client */
    protected $client;
    protected $baseDn;
    protected $identifierAttribute;
    protected $filter;

    public function __construct(Client $client, $baseDn, $identifierAttribute, $filter)
    {
        $this->client = $client;
        $this->baseDn = $baseDn;
        $this->identifierAttribute = $identifierAttribute;
        $this->filter = $filter;
    }

    /**
     * @return LDAPGroup[]
     */
    public function getGroups()
    {
        $groups = array();
        foreach ($this->client->getObjects($this->baseDn, $this->filter) as $record) {
            $groups[] = $this->transformGroupRecord($record);

        }
        return $groups;
    }

    /**
     * @param array $record
     * @return LDAPGroup
     */
    protected function transformGroupRecord(array $record)
    {
        $identifier = $record[$this->identifierAttribute][0];
        return new LDAPGroup($identifier);
    }
}
