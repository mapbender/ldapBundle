## v2.0.2
* Replace UsernameNotFoundException with new UserNotFoundException

## v2.0.1
* Change to ldap_search function instead of ldap_list
 
## v2.0.0
* Refactored LdapBundle to work with Mapbender v4.0

## v1.1.4
* Add toString() function for LdapUser: Fixes storage of LDAP username in database

## v1.1.3
* Add configurable ldap.group.role_prefix parameter (for ACL assignments / checks)

## v1.1.2
* Suppress identities lookup connection error (=Mapbender loading assignable ACL identities) when configured with empty host

## v1.1.1
* Support skipping LDAP user lookup by configuring empty ldap host (suppress ldap connection exception on empty host)

## v1.1.0
* Fix Symfony 4 incompatibilities
* Fix handling of usernames with ldap-escapable characters
* Fix integration with Mapbender ACL assignments

## v1.0.9
* Fix Symfony 3 incompatibilites

