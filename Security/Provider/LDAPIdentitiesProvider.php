<?php

namespace Mapbender\LDAPBundle\Security\Provider;

use Doctrine\Persistence\ManagerRegistry;
use FOM\UserBundle\Component\Ldap;

class LDAPIdentitiesProvider extends \FOM\UserBundle\Component\FOMIdentitiesProvider
{
    /** @var LDAPGroupProvider */
    protected $groupProvider;

    public function __construct(ManagerRegistry $doctrineRegistry,
                                Ldap\UserProvider $ldapUserProvider,
                                LDAPGroupProvider $groupProvider,
                                $userEntityClass)
    {
        parent::__construct($doctrineRegistry, $ldapUserProvider, $userEntityClass);
        $this->groupProvider = $groupProvider;
    }

    public function getAllGroups()
    {
        $groups = $this->getRepository(\FOM\UserBundle\Entity\Group::class)->findAll();
        return \array_merge($groups, $this->groupProvider->getGroups());
    }
}
