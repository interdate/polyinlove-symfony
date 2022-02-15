<?php 

namespace AppBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class FacebookKeyUserProvider implements UserProviderInterface
{
	public function __construct($doctrine)
	{
		$this->doctrine = $doctrine;
	}
	
	public function getUsernameForFacebookId($facebook_id)
	{
		// Look up the username based on the token in the database, via
		// an API call, or do something entirely different

        $user = $this->doctrine->getRepository('AppBundle:User')->findOneBy(array('facebook', $facebook_id));
        if($user){
            return $user->getUsername();
        }
		return null;		
	}
	
	public function saveData($data, $userId){
		
		// This method is NOT required. This is my custom method for checking
		// data sent by API consumer
		
		$conn = $this->doctrine->getConnection();
		
		$sql = "";
		
		$conn->query($sql);
	}

	public function loadUserByUsername($username)
	{
		return new User(
			$username,
			null,
			// the roles for the user - you may choose to determine
			// these dynamically somehow based on the user
			array('ROLE_USER')
		);
	}

	public function refreshUser(UserInterface $user)
	{
		// this is used for storing authentication in the session
		// but in this example, the token is sent in each request,
		// so authentication can be stateless. Throwing this exception
		// is proper to make things stateless
		throw new UnsupportedUserException();
	}

	public function supportsClass($class)
	{
		return 'Symfony\Component\Security\Core\User\User' === $class;
	}
}