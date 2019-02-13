<?php
/**
 * Created by PhpStorm.
 * User: dpatzke
 * Date: 05.02.19
 * Time: 14:20
 */

namespace Mapbender\LDAPBundle\Security\User;
use Symfony\Component\Security\Core\User\UserInterface;

class LDAPUser implements UserInterface
{

    protected $username;
    protected $roles;

    public function __construct($username,Array $roles = ["ROLE_USER"])
    {
        $this->username = $username;
        $this->roles = $roles;
    }


    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
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
    /*
     * This is uses to create proper ACEs in Mapbender
     * */
    public function getClass(){
        return "Mapbender\LDAPBundle\Security\User\LDAPUser";
    }
}
