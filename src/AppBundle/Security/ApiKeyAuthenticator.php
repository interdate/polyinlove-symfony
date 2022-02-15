<?php 

namespace AppBundle\Security;

//use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
	protected $userProvider;
	protected $userId;

	public function __construct(ApiKeyUserProvider $userProvider)
	{		
		$this->userProvider = $userProvider;
	}

	public function createToken(Request $request, $providerKey)
	{	
		$this->userId = $request->attributes->get('userId');
		$apiKey = $request->headers->get('apiKey');
		 
		if (!$apiKey) {
			throw new BadCredentialsException('No API key found');
		}

		return new PreAuthenticatedToken(
			'anon.',
			$apiKey,
			$providerKey
		);
	}

	public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
	{
		$apiKey = $token->getCredentials();
		$username = $this->userProvider->getUsernameForUserIdAndApiKey($this->userId, $apiKey);

		if (!$username) {
			throw new AuthenticationException(
				sprintf('API Key "%s" does not exist.', $apiKey)
			);
		}

		$user = $this->userProvider->loadUserByUsername($username);

		return new PreAuthenticatedToken(
			$user,
			$apiKey,
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
