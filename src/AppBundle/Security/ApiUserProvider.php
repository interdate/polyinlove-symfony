<?php 

namespace AppBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class ApiUserProvider implements UserProviderInterface
{
	public function __construct($doctrine)
	{
		$this->doctrine = $doctrine;
	}

    public function loadUserByUsername($username)
    {
        $username = urldecode(trim((string)$username));
        $user = $this->doctrine->getRepository('AppBundle:User')->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email OR u.phone = :phone')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->setParameter('phone', $username)
            ->getQuery()
            ->getOneOrNullResult();

        

//        if (null === $user) {
//            $message = sprintf(
//                'Unable to find an active user AppBundle:User object identified by "%s".',
//                $username
//            );
//            throw new UsernameNotFoundException($message);
//        }

        return $user;
    }
	
//	public function getUsernameForUserIdAndApiKey($userId, $apiKey)
//	{
//		// Look up the username based on the token in the database, via
//		// an API call, or do something entirely different
//
//        $user = $this->doctrine->getRepository('AppBundle:User')->find($userId);
//        if($apiKey == md5($user->getId()) . md5($user->getPassword())){
//            return $user->getUsername();
//        }
//		return null;
//	}
	
	public function saveData($data, $userId){
		
		// This method is NOT required. This is my custom method for checking
		// data sent by API consumer
		
		$conn = $this->doctrine->getConnection();
		
		$sql = "";
		
		$conn->query($sql);
	}

//	public function loadUserByUsername($username)
//	{
//		return new User(
//			$username,
//			null,
//			// the roles for the user - you may choose to determine
//			// these dynamically somehow based on the user
//			array('ROLE_USER')
//		);
//	}

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