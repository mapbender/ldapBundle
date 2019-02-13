<?php


namespace Mapbender\LDAPBundle\Security\Provider;

use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapClientInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Mapbender\LDAPBundle\Security\User\LDAPUser;


/**
 * Class LdapMultiEncoderUserProvider
 *
 * @package Wheregroup\Component
 * @author  David Patzke <david.patzke@wheregroup.com>
 */
class LDAPUserProvider implements UserProviderInterface
{
    private $ldapClient;
    private $baseDn;
    private $basePW;
    private $searchDn;
    private $searchPassword;
    private $defaultRoles;
    private $defaultSearch;
    private $groupSearchFilter;
    private $groupBaseDN;
    private $group_uid_key;

    /**
     * LdapMultiEncoderUserProvider constructor.
     *
     * @param LdapClientInterface $ldap
     * @param string              $baseDn
     * @param null                $searchDn
     * @param null                $searchPassword
     * @param array               $defaultRoles
     * @param string              $uidKey
     * @param string              $filter
     * @param string              $groupBaseDN
     * @param string              $groupSearchFilter
     * @param string              $group_uid_key
     */
    public function __construct(LdapClientInterface $ldapClient,$baseDn, $basePw, $userDN,$userQuery,$groupBaseDN,$groupQuery, Array $defaultRoles = ['ROLE_USER'], $groupId = 'cn')
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
            $user = $this->ldapClient->find($this->userDN,$userQuery, '(objectClass=*)');

            if($user){

                $groups = $this->defaultRoles;

                $ldapGroupSearchQuery = str_replace('{username}', $username, $this->groupQuery);
                $ldapGroups = $this->ldapClient->find($this->groupBaseDN,$ldapGroupSearchQuery);

                if($ldapGroups) {

                    foreach($ldapGroups as $group){
                        if (!empty($group['cn'][0])) {

                            $groups[] = 'ROLE_' . $group['cn'][0];
                        }

                    }
                }
            } else {
                throw new UsernameNotFoundException(sprintf('Users "%s" groups could not be fetched from LDAP.', $username), 0);
            }


        } catch(\Exception $e){
            throw $e;
            throw new ConnectionException("Connection to LDAP Server could be established", 0, $e);
        }



        return new LdapUser($username, $groups);
    }

    public function supportsClass($class)
    {
        return true;
    }
}


