<?php
/**
 * Created by PhpStorm.
 * User: dpatzke
 * Date: 05.02.19
 * Time: 14:20
 */

namespace Mapbender\LDAPBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class LdapUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    protected $username;
    protected $roles;

    public function __construct($username, Array $roles = ['ROLE_USER'])
    {
        $this->username = $username;
        $this->roles = $roles;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): string
    {
        return '';
    }

    public function getSalt()
    {
        return '';
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
        return false;
    }

    public function __toString()
    {
        return $this->getUsername() ?: '';
    }

    public function getId()
    {
        return null;
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
    }
}
