<?php

namespace Mapbender\LDAPBundle\Security\Permission;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;
use Symfony\Component\Security\Core\User\UserInterface;
use Mapbender\LDAPBundle\Security\LdapClient;

trait SubjectDomainLdapTrait
{
    protected $client;
    protected $baseDn;
    protected $query;
    protected $id;
    protected $commonName;

    public function __construct(LdapClient $client, $baseDn, $query, $id, $commonName)
    {
        $this->client = $client;
        $this->baseDn = $baseDn;
        $this->query = $query;
        $this->id = $id;
        $this->commonName = $commonName;
    }
}
