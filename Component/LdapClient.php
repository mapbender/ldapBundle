<?php


namespace Mapbender\LDAPBundle\Component;


use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;
use Symfony\Component\Ldap\Adapter\QueryInterface;
use Symfony\Component\Ldap\LdapInterface;

class LdapClient extends \FOM\UserBundle\Component\Ldap\Client implements LdapInterface
{
    /** @var \Symfony\Component\Ldap\Adapter\AdapterInterface */
    protected $adapter;

    public function __construct($host, $port, $version, $useSsl, $useTls, $bindDn, $bindPassword)
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
        parent::__construct($host, $port, $version, $bindDn, $bindPassword);
    }

    public function bind($dn = null, $password = null)
    {
        $this->adapter->getConnection()->bind($dn ?: $this->bindDn, $password ?: $this->bindPassword);
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

    /**
     *  FOM client compatibility
     * @see \FOM\UserBundle\Component\Ldap\Client::getObjects
     *
     * @param $baseDn
     * @param $filter
     * @return \array[][]
     */
    public function getObjects($baseDn, $filter)
    {
        if (!$this->adapter->getConnection()->isBound()) {
            $this->adapter->getConnection()->bind($this->bindDn, $this->bindPassword);
        }
        $query = $this->adapter->createQuery($baseDn, $filter, array(
            'scope' => QueryInterface::SCOPE_ONE,
        ));
        $records = array();
        foreach ($query->execute() as $entry) {
            $records[] = $entry->getAttributes();
        }
        return $records;
    }

    public function getHost()
    {
        return $this->host;
    }
}
