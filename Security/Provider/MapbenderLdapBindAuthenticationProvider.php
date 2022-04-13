<?php
/**
 * Created by PhpStorm.
 * User: dpatzke
 * Date: 13.02.19
 * Time: 14:18
 */

namespace Mapbender\LDAPBundle\Security\Provider;

use Symfony\Component\Ldap\LdapClientInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Provider\LdapBindAuthenticationProvider;

class MapbenderLdapBindAuthenticationProvider extends  LdapBindAuthenticationProvider
{
    private $encoderFactory;

    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, $providerKey, LdapClientInterface $ldap, EncoderFactoryInterface $encoderFactory,$dnString = '{cn=username}', $hideUserNotFoundExceptions = true, $userQuery)
    {
        parent::__construct( $userProvider,  $userChecker, $providerKey,  $ldap, $dnString, $hideUserNotFoundExceptions);

        $this->encoderFactory = $encoderFactory ;

        // support Mapbender < 3.2.x (Symfony 2.8)
        if (method_exists(get_parent_class($this), 'setQueryString')) {
            $this->setQueryString($userQuery);
        }
    }



    /**
     * {@inheritdoc}
     */
    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
    {

        $password = $token->getCredentials();
        try{
            parent::checkAuthentication($user, $token);
        } catch(BadCredentialsException $e){

            try {
                if (!$this->encoderFactory->getEncoder($user)->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
                    throw new BadCredentialsException('The presented password is invalid.');
                }
            } catch (\Exception $e){
                throw new BadCredentialsException('The presented password is invalid.');
            }
        }
    }


}
