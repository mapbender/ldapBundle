<?php

namespace Mapbender\LDAPBundle\Security\Permission;

use Symfony\Component\Ldap\Adapter\QueryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\QueryBuilder;
use Mapbender\LDAPBundle\Security\LdapUser;
use FOM\UserBundle\Entity\Permission;
use FOM\UserBundle\Entity\User;
use FOM\UserBundle\Security\Permission\AbstractSubjectDomain;
use FOM\UserBundle\Security\Permission\SubjectInterface;
use FOM\UserBundle\Security\Permission\AssignableSubject;

class SubjectDomainLdapUser extends AbstractSubjectDomain
{
    use SubjectDomainLdapTrait;

    const SLUG = 'ldap_user';

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function buildWhereClause(QueryBuilder $q, ?UserInterface $user): void
    {
        if ($user !== null and $user instanceof LdapUser) {
            $q->orWhere("p.subjectDomain = '" . self::SLUG . "' AND p.subject = :username")
                ->setParameter('username', $user->getUsername())
            ;
        }
    }

    public function getTitle(SubjectInterface $subject): string
    {
        return $subject->getSubject();
    }

    public function getAssignableSubjects(): array
    {
        $this->client->bind();
        $query = $this->client->query($this->baseDn, $this->query, [
            'scope' => QueryInterface::SCOPE_ONE,
        ]);
        $ldapUsers = [];

        foreach ($query->execute() as $entry) {
            $ldapUsers[] = [
                'id' => $entry->getAttribute($this->id)[0],
                'cn' => $entry->getAttribute($this->commonName)[0],
            ];
        }

        return array_map(
            fn($user) => new AssignableSubject(
                self::SLUG,
                $user['cn'],
                $this->getIconClass(),
                null,
                null,
                $user['id']
            ),
            $ldapUsers
        );
    }

    public function supports(mixed $subject, ?string $action = null): bool
    {
        return $subject instanceof LdapUser;
    }

    public function populatePermission(Permission $permission, mixed $subject): void
    {
        parent::populatePermission($permission, $subject);
        $permission->setUser($subject);
    }
}
