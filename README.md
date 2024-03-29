## Mapbender LDAP

The LDAP Bundle provides LDAP integration for Mapbender.

### 1. Installation

Install Mapbender LDAP via Composer:

```sh
composer require mapbender/ldapbundle
```
Register the bundle in AppKernel.php:

```php
new Mapbender\LDAPBundle\MapbenderLDAPBundle(),
```

Enable LDAP extension for php.

### 2. Configuration

In the `security.yml` add the ldapProvider and some firewall configuration: 

```yml
...

providers:
  ldapProvider:
    id: LDAPUserProvider
  main:
    entity:
      class: FOM\UserBundle\Entity\User
      property: username
  chain_provider:
    chain:
      providers: ['ldapProvider', 'main']
...

firewalls:
    
  ...
    
  secured_area:
    pattern:    ^/
    anonymous: ~
    provider: main
    mapbender_ldap:
      login_path: /user/login
      check_path: /user/login/check
      provider: ldapProvider
    form_login:
      check_path: /user/login/check
      login_path: /user/login
      csrf_token_generator: security.csrf.token_manager
    logout:
      path:   /user/logout
      target: /
    
...
```
Add your LDAP server settings at the bottom of the `parameters.yml`:

```yml
ldap.host: ldap.example.com
ldap.port: 389
ldap.version: 3
ldap.useSSL: false
ldap.useTLS: false
ldap.bind.dn: cn=user,dc=example,dc=com
ldap.bind.pwd: passwort

ldap.user.nameAttribute: cn
ldap.user.dn: cn=users,dc=example,dc=com
ldap.user.baseDn: dc=example,dc=com
ldap.user.adminFilter: (objectClass=*)
ldap.user.query: (&(cn={username})(objectclass=user))

ldap.group.nameAttribute: ~
ldap.group.baseDn: ou=groups,dc=example,dc=com
ldap.group.adminFilter: (objectClass=*)
ldap.group.id: cn
ldap.group.query: member=cn={username},ou=user,dc=example,dc=com
```
