<?php

namespace Mapbender\LDAPBundle\Security;

use Symfony\Component\Ldap\Adapter\QueryInterface;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Mapbender\LDAPBundle\Security\LdapClient;
use Mapbender\LDAPBundle\Security\LdapUser;

/**
 * @author  David Patzke <david.patzke@wheregroup.com>
 */
class LdapUserProvider implements UserProviderInterface
{
    private $client;
    private $baseDn;
    private $query;
    private $groupKey;
    private $baseDnGroup;
    private $queryGroup;
    private $idGroup;
    private $defaultRoles;

    /**
     * @param LdapClient $client
     * @param string $baseDn
     * @param string $query
     * @param string $groupKey
     * @param string $baseDnGroup
     * @param string $queryGroup
     * @param string $idGroup
     * @param string[] $defaultRoles
     */
    public function __construct(LdapClient $client, $baseDn, $query, $groupKey, $baseDnGroup, $queryGroup, $idGroup, $defaultRoles = ['ROLE_USER'])
    {
        $this->client = $client;
        $this->baseDn = $baseDn;
        $this->query = $query;
        $this->groupKey = $groupKey;
        $this->baseDnGroup = $baseDnGroup;
        $this->queryGroup = $queryGroup;
        $this->idGroup = $idGroup;
        $this->defaultRoles = $defaultRoles;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof LdapUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return new LdapUser($user->getUsername(), $user->getRoles());
    }

    public function loadUserByIdentifier(string $username): UserInterface
    {
        // NOTE: when wrapped in a chain provider, UserNameNotFoundException is caught and silently
        //       skips to the next provider, if any
        /** @see \Symfony\Component\Security\Core\User\ChainUserProvider::loadUserByUsername() */
        if (!$this->client->getHost()) {
            throw new UsernameNotFoundException('LDAP lookup disabled with empty host configuration', 0);
        }

        $this->client->bind();
        $queryString = str_replace('{username}', $this->client->escape($username, '', LDAP_ESCAPE_FILTER), $this->query);
        $results = $this->client->query($this->baseDn, $queryString)->execute();

        if ($results->count() === 1) {
            $roles = $this->findLdapUserRoles($results[0]);
            return new LdapUser($username, $roles);
        } else {
            throw new UsernameNotFoundException(sprintf('User "%s" could not be fetched from LDAP.', $username), 0);
        }
    }

    public function supportsClass($class)
    {
        return true;
    }

    protected function findLdapUserRoles(Entry $user)
    {
        $roles = [];

        foreach ($user->getAttribute($this->groupKey) as $role) {
            $queryString = str_replace('{groupname}', $this->client->escape($role, '', LDAP_ESCAPE_FILTER), $this->queryGroup);
            $results = $this->client->query($this->baseDnGroup, $queryString)->execute();
            if ($results->count() === 1) {
                $roles[] = $results[0]->getAttribute($this->idGroup)[0];
            }
        }

        return \array_unique(\array_merge($this->defaultRoles, $roles));
    }
}
