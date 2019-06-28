<?php

namespace Mapbender\LDAPBundle\Security\Provider;
use Mapbender\LDAPBundle\Security\User\LDAPUser as User;
use Mapbender\LDAPBundle\Security\User\LDAPGroup as Group;
use Mapbender\LDAPBundle\Exceptions\MissconfiguredLDAPClientException;
class LDAPIdentitiesProvider extends \FOM\UserBundle\Component\FOMIdentitiesProvider
{

    public function getAllUsers()
    {

        $user = parent::getAllUsers();
        try {
            $nameAttribute = $this->container->getParameter('ldap.user.nameAttribute');
            $userDn = $this->container->getParameter('ldap.user.baseDn');
            $userQuery = $this->container->getParameter('ldap.user.adminFilter');
        } catch (\Exception $e) {
            throw new MissconfiguredLDAPClientException();
        }

        $ldapClient = $this->getLdapConnection();
        $ldapUserList = $ldapClient->find($userDn, $userQuery);

        if ($ldapUserList !== null) {

            foreach (array_slice($ldapUserList, 2) as $ldapUser) {
                if(isset($ldapUser[$nameAttribute][0])){
                    $user[] = new User($ldapUser[$nameAttribute][0]);
                }
                
            }

        }

        return $user;

    }

    public function getLdapConnection()
    {
        $ldapClient = $this->container->get('ldapClient');
        try {
            $ldapBindDN = $this->container->getParameter('ldap.bind.dn');
            $ldapBindPw = $this->container->getParameter('ldap.bind.pwd');
        } catch (\Exception $e) {
            throw new MissconfiguredLDAPClientException();
        }


        $ldapClient->bind($ldapBindDN, $ldapBindPw);
        return $ldapClient;

    }

    public function getAllGroups()
    {
        $groups = parent::getAllGroups();

        try {
            $groupDn = $this->container->getParameter('ldap.group.baseDn');
            $groupFilter = $this->container->getParameter('ldap.group.adminFilter');
            $groupIdentifier = $this->container->hasParameter('ldap.group.id') ? $this->container->getParameter('ldap.group.id') : 'cn';
        } catch (\Exception $e) {
            throw new MissconfiguredLDAPClientException();
        }

        $ldapClient = $this->getLdapConnection();
        $ldapGroupList = $ldapClient->find($groupDn, $groupFilter);
        if ($ldapGroupList != null) {
            foreach (array_slice($ldapGroupList, 2) as $ldapGroup) {
                isset($ldapGroup[$groupIdentifier]) ? new Group($ldapGroup[$groupIdentifier][0]) : null;
               
            }

        }

        return $groups;
    }

}
