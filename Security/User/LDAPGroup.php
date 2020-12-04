<?php


namespace Mapbender\LDAPBundle\Security\User;

use FOM\UserBundle\Entity\Group as Group;

class LDAPGroup extends Group {

    protected $title;

    public function __construct($title)
    {
        $this->title = $title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
      return $this->title;
    }

    public function getAsRole()
    {
       return   'ROLE_GROUP_' . strtoupper($this->getTitle());
    }
}
