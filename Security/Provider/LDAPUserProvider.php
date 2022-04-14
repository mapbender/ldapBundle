<?php


namespace Mapbender\LDAPBundle\Security\Provider;

use Symfony\Component\Ldap\LdapInterface;
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
    private $ldapClient;
    /** @var LDAPGroupProvider */
    protected $groupProvider;
    private $baseDn;
    private $basePw;
    private $userDN;
    private $defaultRoles;
    private $userQuery;

    /**
     * LdapMultiEncoderUserProvider constructor.
     *
     * @param LdapInterface $ldapClient
     * @param LdapGroupProvider $groupProvider
     * @param string              $baseDn
     * @param string|null $basePw
     * @param string $userDN
     * @param string $userQuery
     * @param string[] $defaultRoles
     */
    public function __construct(LdapInterface $ldapClient, LDAPGroupProvider $groupProvider,
                                $baseDn, $basePw, $userDN, $userQuery, Array $defaultRoles = ['ROLE_USER'])
    {
        $this->ldapClient        = $ldapClient;
        $this->groupProvider = $groupProvider;
        $this->baseDn            = $baseDn;
        $this->basePw            = $basePw;
        $this->userDN            = $userDN;
        $this->userQuery         = $userQuery;
        $this->defaultRoles      = $defaultRoles;
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
        try {

            $this->ldapClient->bind($this->baseDn,$this->basePw);
            $userQuery = str_replace('{username}', $this->ldapClient->escape($username, '', LDAP_ESCAPE_FILTER), $this->userQuery);
            $matches = $this->ldapClient->query($this->userDN, $userQuery)->execute()->toArray();
            $user = $matches[0];

            if ($user) {
                $roles = \array_unique(\array_merge($this->defaultRoles, $this->groupProvider->getRolesByUserEntry($user, $username)));
                return new LdapUser($username, $roles);
            } else {
                throw new UsernameNotFoundException(sprintf('Users "%s" groups could not be fetched from LDAP.', $username), 0);
            }


        } catch(\Exception $e){
            //This is in fact not a UsernameNotFoundException but otherwise the chain user provider will not work because it catches only this particular exception
            throw new UsernameNotFoundException("Connection to LDAP Server could be established", 0, $e);
        }



    }

    public function supportsClass($class)
    {
        return true;
    }
}


