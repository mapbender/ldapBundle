<?php


namespace Mapbender\LDAPBundle\Component;


use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;
use Symfony\Component\Ldap\LdapInterface;

class LdapClient implements LdapInterface
{
    /** @var \Symfony\Component\Ldap\Adapter\AdapterInterface */
    protected $adapter;

    public function __construct($host, $port, $version, $useSsl, $useTls)
    {
        // Create an ldap adapter (Symfony >= 3)
        /** @see \Symfony\Component\Ldap\Adapter\AbstractConnection::configureOptions for possible options */
        $adapterOptions = array_filter(array(
            'host' => $host,
            'version' => $version,
            'port' => $port,
        ));
        if ($useSsl) {
            $adapterOptions['encryption'] = 'ssl';
        }
        if ($useTls) {
            $adapterOptions['encryption'] = 'tls';
        }
        $this->adapter = new Adapter($adapterOptions);
    }

    public function bind($dn = null, $password = null)
    {
        $this->adapter->getConnection()->bind($dn, $password);
    }

    public function query($dn, $query, array $options = [])
    {
        return $this->adapter->createQuery($dn, $query, $options);
    }

    public function getEntryManager()
    {
        return $this->adapter->getEntryManager();
    }

    public function escape($subject, $ignore = '', $flags = 0)
    {
        return $this->adapter->escape($subject, $ignore, $flags);
    }
}
