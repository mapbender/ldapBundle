<?php


namespace Mapbender\LDAPBundle\Security\User;

use Mapbender\LDAPBundle\Security\User\MapbenderGroupInterface;

class LDAPGroup implements MapbenderGroupInterface {

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
       return   'ROLE_' . strtoupper($this->getTitle());
    }
}
