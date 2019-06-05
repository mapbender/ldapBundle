<?php

namespace Mapbender\LDAPBundle\Security\User;


interface MapbenderGroupInterface {

    public function setTitle($title);
    public function getTitle();
    public function getAsRole();


}
