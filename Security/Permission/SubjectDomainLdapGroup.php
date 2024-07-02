<?php

namespace Mapbender\LDAPBundle\Security\Permission;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Ldap\Adapter\QueryInterface;
use Doctrine\ORM\QueryBuilder;
use Mapbender\LDAPBundle\Security\LdapUser;
use Mapbender\LDAPBundle\Security\LdapGroup;
use FOM\UserBundle\Entity\Permission;
use FOM\UserBundle\Security\Permission\AbstractSubjectDomain;
use FOM\UserBundle\Security\Permission\SubjectInterface;
use FOM\UserBundle\Security\Permission\AssignableSubject;

class SubjectDomainLdapGroup extends AbstractSubjectDomain
{
    use SubjectDomainLdapTrait;

    const SLUG = 'ldap_group';

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function buildWhereClause(QueryBuilder $q, ?UserInterface $user): void
    {
        if ($user !== null and $user instanceof LdapUser) {
            $q->orWhere("p.subjectDomain = '" . self::SLUG . "' AND p.subject IN (:groups)")
                ->setParameter('groups', $user->getRoles())
            ;
        }
    }

    function getIconClass(): string
    {
        return 'fas fa-users';
    }

    public function getTitle(SubjectInterface $subject): string
    {
        return $subject->getSubject();
    }

    public function getAssignableSubjects(): array
    {
        $this->client->bind();
        $query = $this->client->query($this->baseDn, $this->query, [
            'scope' => QueryInterface::SCOPE_SUB,
        ]);
        $ldapGroups = [];

        foreach ($query->execute() as $entry) {
            $ldapGroups[] = [
                'id' => $entry->getAttribute($this->id)[0],
                'cn' => $entry->getAttribute($this->commonName)[0],
            ];
        }


        return array_map(
            fn($group) => new AssignableSubject(
                self::SLUG,
                $group['cn'],
                $this->getIconClass(),
                null,
                null,
                $group['id']
            ),
            $ldapGroups
        );
    }

    public function supports(mixed $subject, ?string $action = null): bool
    {
        return $subject instanceof LdapGroup;
    }

    public function populatePermission(Permission $permission, mixed $subject): void
    {
        parent::populatePermission($permission, $subject);
        $permission->setGroup($subject);
    }
}
