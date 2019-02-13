<?php
/**
 * Created by PhpStorm.
 * User: dpatzke
 * Date: 13.02.19
 * Time: 14:18
 */

namespace Mapbender\LDAPBundle\Security\Provider;


use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapClientInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Symfony\Component\Security\Core\Authentication\Provider\LdapBindAuthenticationProvider;

class MapbenderLdapBindAuthenticationProvider extends  LdapBindAuthenticationProvider
{

    private $userProvider;
    private $ldap;
    private $dnString;
    private $encoderFactory;

    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, $providerKey, LdapClientInterface $ldap, EncoderFactoryInterface $encoderFactory,$dnString = '{cn=username}', $hideUserNotFoundExceptions = true)
    {
        parent::__construct( $userProvider,  $userChecker, $providerKey,  $ldap, $dnString, $hideUserNotFoundExceptions);

        $this->userProvider = $userProvider;
        $this->ldap = $ldap;
        $this->encoderFactory = $encoderFactory ;
        $this->dnString = $dnString;

    }



    /**
     * {@inheritdoc}
     */
    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
    {
        $username = $token->getUsername();
        $password = $token->getCredentials();

        if ('' === (string) $password) {
            throw new BadCredentialsException('The presented password must not be empty.');
        }

        try {
            $username = $this->ldap->escape($username, '', LDAP_ESCAPE_DN);

            $dn = str_replace('{username}', $username, $this->dnString);

            $this->ldap->bind($dn, $password);
        } catch (ConnectionException $e) {

            try {
                if (!$this->encoderFactory->getEncoder($user)->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
                    throw new BadCredentialsException('The presented password is invalid.');
                }
            }catch (ConnectionException $e){
                throw new BadCredentialsException('The presented password is invalid.');
            }

        }
    }


}
