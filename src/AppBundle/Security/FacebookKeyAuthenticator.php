<?php 

namespace AppBundle\Security;

use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class FacebookKeyAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
	protected $userProvider;
	protected $facebook_id;//$userId

	public function __construct(FacebookKeyUserProvider $userProvider)
	{		
		$this->userProvider = $userProvider;
	}

	public function createToken(Request $request, $providerKey)
	{	
		//$this->userId = $request->attributes->get('userId');
        $this->facebook_id = $request->request->get('facebook', false);
		//$apiKey = $request->headers->get('apiKey')
		 
//		if (!$this->facebook_id) {
//			throw new BadCredentialsException('No API key found');
//			// or to just skip api key authentication
//			// return null;
//		}

		return new PreAuthenticatedToken(
			'anon.',
            $this->facebook_id,
			$providerKey
		);
	}

	public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
	{
        $facebook_id = $token->getCredentials();
		$username = $this->userProvider->getUsernameForFacebookId($this->facebook_id);

		if (!$username) {
			throw new AuthenticationException(
				sprintf('API Key "%s" does not exist.'/*, $apiKey*/)
			);
		}

		$user = $this->userProvider->loadUserByUsername($username);

		return new PreAuthenticatedToken(
			$user,
            $facebook_id,
			$providerKey,
			$user->getRoles()
		);
	}

	public function supportsToken(TokenInterface $token, $providerKey)
	{
		return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
	}
	
	public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
	{		
		return new Response("Authentication Failed.", 403);
	}
}
