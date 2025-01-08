<?php

namespace Mapbender\LDAPBundle\Security;

use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;

class LdapClient
{
    protected $adapter;
    protected $host;
    protected $bindDn;
    protected $bindPwd;

    public function __construct($host, $port, $version, $encryption, $bindDn, $bindPwd)
    {
        $adapterOptions = [
            'host' => $host,
            'port' => $port,
            'version' => $version,
            'encryption' => $encryption,
        ];
        $this->adapter = new Adapter($adapterOptions);
        $this->host = $host;
        $this->bindDn = $bindDn;
        $this->bindPwd = $bindPwd;
    }

    public function bind($dn = null, $password = null)
    {
        $this->adapter->getConnection()->bind($dn ?: $this->bindDn, $password ?: $this->bindPwd);
    }

    public function query($dn, $query, array $options = [])
    {
        return $this->adapter->createQuery($dn, $query, $options);
    }

    public function escape($subject, $ignore = '', $flags = 0)
    {
        return $this->adapter->escape($subject, $ignore, $flags);
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getBindDn()
    {
        return $this->bindDn;
    }

    public function getBindPwd()
    {
        return $this->bindPwd;
    }
}
