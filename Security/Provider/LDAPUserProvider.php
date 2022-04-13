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
    private $baseDn;
    private $basePw;
    private $userDN;
    private $defaultRoles;
    private $userQuery;
    private $groupQuery;
    private $groupBaseDN;
    private $groupId;

    /**
     * LdapMultiEncoderUserProvider constructor.
     *
     * @param LdapInterface $ldapClient
     * @param string              $baseDn
     * @param string|null $basePw
     * @param string $userDN
     * @param string $userQuery
     * @param string              $groupBaseDN
     * @param string $groupQuery
     * @param string[] $defaultRoles
     * @param string $groupId
     */
    public function __construct(LdapInterface $ldapClient,$baseDn, $basePw, $userDN,$userQuery,$groupBaseDN,$groupQuery, Array $defaultRoles = ['ROLE_USER'], $groupId = 'cn')
    {

        $this->ldapClient        = $ldapClient;
        $this->baseDn            = $baseDn;
        $this->basePw            = $basePw;
        $this->userDN            = $userDN;
        $this->userQuery         = $userQuery;
        $this->groupQuery        = $groupQuery;
        $this->defaultRoles      = $defaultRoles;
        $this->groupBaseDN       = $groupBaseDN;
        $this->groupId = $groupId;
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
            $username = $this->ldapClient->escape($username, '', LDAP_ESCAPE_FILTER);
            $userQuery = str_replace('{username}', $username, $this->userQuery);
            $matches = $this->ldapClient->query($this->userDN, $userQuery)->execute()->toArray();
            $user = $matches[0];
            
            if ($user) {
                $ldapGroupSearchQuery = str_replace('{userDN}', $user->getDn(), $this->groupQuery);

                $groups = $this->defaultRoles;
                
                
                $ldapGroups = $this->ldapClient->query($this->groupBaseDN, $ldapGroupSearchQuery)->execute();
                foreach ($ldapGroups as $group){
                    if (!empty($group->getAttribute('cn'))) {
                        $groups[] = 'ROLE_GROUP_' . strtoupper($group->getAttribute('cn')[0]);
                    }

                }
            } else {
                throw new UsernameNotFoundException(sprintf('Users "%s" groups could not be fetched from LDAP.', $username), 0);
            }


        } catch(\Exception $e){
            //This is in fact not a UsernameNotFoundException but otherwise the chain user provider will not work because it catches only this particular exception
            throw new UsernameNotFoundException("Connection to LDAP Server could be established", 0, $e);
        }



        return new LdapUser($username, $groups);
    }

    public function supportsClass($class)
    {
        return true;
    }
}


