<?php

namespace Mapbender\LDAPBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Ldap\Adapter\QueryInterface;
use Symfony\Component\Ldap\Security\LdapBadge;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Ldap\Security\LdapAuthenticator;
use Symfony\Component\Security\Http\Authenticator\InteractiveAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\EntryPoint\Exception\NotAnEntryPointException;
use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;
use Mapbender\LDAPBundle\Security\LdapClient;

class MapbenderLdapAuthenticator extends AbstractLoginFormAuthenticator
{
    private $authenticator;
    private $client;
    private $bindDn;
    private $bindPwd;
    private $baseDn;
    private $query;

    public function __construct(AuthenticatorInterface $authenticator, LdapClient $client, $baseDn, $query)
    {
        $this->authenticator = $authenticator;
        $this->client = $client;
        $this->baseDn = $baseDn;
        $this->query = $query;
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->httpUtils->generateUri($request, $this->options['login_path']);
    }

    public function supports(Request $request): bool
    {
        return $this->authenticator->supports($request);
    }

    public function authenticate(Request $request): Passport
    {
        $passport = $this->authenticator->authenticate($request);
        $username = $passport->getUser()->getUsername();
        $this->client->bind();
        $queryString = str_replace('{username}', $this->client->escape($username, '', LDAP_ESCAPE_FILTER), $this->query);
        $query = $this->client->query($this->baseDn, $queryString, [
            'scope' => QueryInterface::SCOPE_SUB,
        ]);
        $results = $query->execute();

        if ($results->count() === 1) {
            $bindDn = $this->client->getBindDn();
            $bindPwd = $this->client->getBindPwd();
            $passport->addBadge(new LdapBadge('Symfony\Component\Ldap\Ldap', $this->baseDn, $bindDn, $bindPwd, $this->query));
            return $passport;
        }

        return $passport;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return $this->authenticator->onAuthenticationSuccess($request, $token, $firewallName);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return $this->authenticator->onAuthenticationFailure($request, $exception);
    }
}


