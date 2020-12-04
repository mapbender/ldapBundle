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
            $user = $this->ldapClient->find($this->userDN,$userQuery, '');
            
            if($user){
                // We assume here that our username has to be unique otherwise login would not work in general.
                // LDAP search gives us a result set, so our user has to be the first entry and using user[0] should be save.
                // According to RFC https://tools.ietf.org/html/rfc4511#page-20 a search result must provide the <DN> attribute
                // and resulting from that we can always be save that $user[0]['dn']; will have the correct value!
                $ldapGroupSearchQuery = str_replace('{userDN}', $user[0]['dn'], $this->groupQuery);

                $groups = $this->defaultRoles;
                
                
                $ldapGroups = $this->ldapClient->find($this->groupBaseDN,$ldapGroupSearchQuery);

                if($ldapGroups) {

                    foreach($ldapGroups as $group){
                        if (!empty($group['cn'][0])) {

                            $groups[] = 'ROLE_GROUP_' . strtoupper($group['cn'][0]);
                        }

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


