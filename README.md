## Mapbender LDAP

The LDAP Bundle provides LDAP integration for Mapbender.

### 1. Installation

Install Mapbender LDAP via Composer:

```sh
composer require mapbender/ldapbundle
```
Register the bundle in bundles.php:

```php
Mapbender\LDAPBundle\MapbenderLDAPBundle::class => ['all' => true],
```

Enable the LDAP extension for php.

### 2. Configuration

In the `security.yaml` add the ldap- and chain-provider, some firewall configuration and a password hasher for the LdapUser:

```yml
...

providers:
  main:
    entity:
      class: FOM\UserBundle\Entity\User
      property: username
  ldap_provider:
    id: 'mapbender.ldap.user_provider'
  all_users:
    chain:
      providers: ['main', 'ldap_provider']
...

firewalls:

  ...

  secured_area:
    pattern: ^/
    entry_point: form_login
    provider: all_users
    custom_authenticators:
      - 'mapbender.ldap.authenticator'
    form_login:
      check_path: /user/login/check
      login_path: /user/login
      enable_csrf: true
    form_login_ldap:
      check_path: /user/login/check
      login_path: /user/login
      enable_csrf: true
    logout:
      path: /user/logout
      target: /

...

password_hashers:
  FOM\UserBundle\Entity\User: sha512
  Mapbender\LDAPBundle\Security\LdapUser: auto

...
```

Add your LDAP server settings at the bottom of the `parameters.yaml`:

```yml
ldap.host: ldap.example.com
ldap.port: 389
ldap.version: 3
ldap.encryption: none # <ssl|tls|none>
ldap.bind.dn: read@example.com
ldap.bind.pwd: passwort

ldap.user.baseDn: cn=users,dc=example,dc=com
ldap.user.query: (&(sAMAccountName={username})(objectClass=user))
ldap.user.adminQuery: (objectClass=user)
ldap.user.id: sAMAccountName
ldap.user.commonName: cn
ldap.user.groupKey: memberOf

ldap.group.baseDn: ou=groups,dc=example,dc=com
ldap.group.query: (&(distinguishedName={groupname})(objectClass=group))
ldap.group.adminQuery: (objectClass=group)
ldap.group.id: sAMAccountName
ldap.group.commonName: cn
ldap.group.defaultRoles: [ROLE_USER] # this should be ROLE_USER in most cases
```

---
### Follow these instructions if you use Mapbender v3.3.5 or older:

### 1. Installation

Install Mapbender LDAP via Composer:

```sh
composer require mapbender/ldapbundle:v1.1.4
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
