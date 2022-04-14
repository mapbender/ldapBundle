<?php


namespace Mapbender\LDAPBundle\Security\Provider;


use FOM\UserBundle\Component\Ldap\Client;
use Mapbender\LDAPBundle\Component\LdapClient;
use Mapbender\LDAPBundle\Security\User\LDAPGroup;
use Symfony\Component\Ldap\Entry;

class LDAPGroupProvider
{
    /** @var Client */
    protected $client;
    protected $baseDn;
    protected $identifierAttribute;
    protected $filter;
    protected $queryTemplate;

    public function __construct(LdapClient $client, $baseDn, $identifierAttribute, $filter, $queryTemplate)
    {
        $this->client = $client;
        $this->baseDn = $baseDn;
        $this->identifierAttribute = $identifierAttribute;
        $this->filter = $filter;
        $this->queryTemplate = $queryTemplate;
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

    /**
     * @param Entry $user
     * @param string $name
     * @return string[]
     */
    public function getRolesByUserEntry(Entry $user, $name)
    {
        $query = $this->queryTemplate;
        $query = \str_replace('{userDN}', $this->client->escape($user->getDn(), LDAP_ESCAPE_FILTER), $query);
        $query = \str_replace('{username}', $this->client->escape($name, LDAP_ESCAPE_FILTER), $query);

        $roleNames = array();
        $ldapGroups = $this->client->query($this->baseDn, $query)->execute();
        foreach ($ldapGroups as $group) {
            $groupIds = $group->getAttribute($this->identifierAttribute);
            if ($groupIds) {
                $roleNames[] = 'ROLE_GROUP_' . strtoupper($groupIds[0]);
            }
        }
        return $roleNames;
    }
}
