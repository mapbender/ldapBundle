<?php
/**
 * Created by PhpStorm.
 * User: dpatzke
 * Date: 08.02.19
 * Time: 11:44
 */

namespace Mapbender\LDAPBundle\Exceptions;


class MissconfiguredLDAPClientException extends \Exception
{

    public function __toString()
    {
        parent::__toString(); // TODO: Change the autogenerated stub
    }
}