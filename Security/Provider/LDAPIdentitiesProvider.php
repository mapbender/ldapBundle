<?php

namespace Mapbender\LDAPBundle\Security\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Mapbender\LDAPBundle\Security\User\LDAPUser as User;
use Mapbender\LDAPBundle\Security\User\LDAPGroup as Group;
use Mapbender\LDAPBundle\Exceptions\MissconfiguredLDAPClientException;
use FOM\UserBundle\Component\Ldap;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class LDAPIdentitiesProvider extends \FOM\UserBundle\Component\FOMIdentitiesProvider
{
    protected $container;

    public function __construct(ManagerRegistry $doctrineRegistry, Ldap\UserProvider $ldapUserProvider, $userEntityClass, Container $container)
    {
        parent::__construct($doctrineRegistry, $ldapUserProvider, $userEntityClass);
        $this->container = $container;
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
                if(isset($ldapGroup[$groupIdentifier])){
                    $groups[] = new Group($ldapGroup[$groupIdentifier][0]);
                }


            }

        }

        return $groups;
    }

}
