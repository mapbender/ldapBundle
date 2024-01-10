<?php


namespace Mapbender\LDAPBundle\Security\Provider;

use Mapbender\LDAPBundle\Component\LdapClient;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Mapbender\LDAPBundle\Security\User\LDAPUser;

/**
 * @author  David Patzke <david.patzke@wheregroup.com>
 */
class LDAPUserProvider implements UserProviderInterface
{
    /** @var LdapClient */
    private $ldapClient;
    /** @var LDAPGroupProvider */
    protected $groupProvider;
    private $userDN;
    private $defaultRoles;
    private $userQuery;

    /**
     * @param LdapClient $ldapClient
     * @param LdapGroupProvider $groupProvider
     * @param string $userDN
     * @param string $userQuery
     * @param string[] $defaultRoles
     */
    public function __construct(LdapClient $ldapClient,
                                LDAPGroupProvider $groupProvider,
                                $userDN,
                                $userQuery,
                                Array $defaultRoles = ['ROLE_USER'])
    {
        $this->ldapClient = $ldapClient;
        $this->groupProvider = $groupProvider;
        $this->userDN = $userDN;
        $this->userQuery = $userQuery;
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

    public function loadUserByUsername($username)
    {
        // NOTE: when wrapped in a chain provider, UserNameNotFoundException is caught and silently
        //       skips to the next provider, if any
        /** @see \Symfony\Component\Security\Core\User\ChainUserProvider::loadUserByUsername() */
        if (!$this->ldapClient->getHost()) {
            throw new UsernameNotFoundException('LDAP lookup disabled with empty host configuration', 0);
        }

        $this->ldapClient->bind();
        $userQuery = str_replace('{username}', $this->ldapClient->escape($username, '', LDAP_ESCAPE_FILTER), $this->userQuery);
        $matches = $this->ldapClient->query($this->userDN, $userQuery)->execute()->toArray();

        if ($matches) {
            $roles = \array_unique(\array_merge($this->defaultRoles, $this->groupProvider->getRolesByUserEntry($matches[0], $username)));
            return new LdapUser($username, $roles);
        } else {
            throw new UsernameNotFoundException(sprintf('User "%s" could not be fetched from LDAP.', $username), 0);
        }
    }

    public function supportsClass($class)
    {
        return true;
    }
}
