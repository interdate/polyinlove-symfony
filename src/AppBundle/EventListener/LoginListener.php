<?php
namespace AppBundle\EventListener;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\User;


/**
 * Listener that updates the last activity of the authenticated user
 */
class LoginListener
{
    protected $tokenStorage;
    protected $entityManager;

    public function __construct(TokenStorage $tokenStorage, EntityManager $entityManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;

    }

    /**
     * Update the user "lastLogin"
     * @param  InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {

//        var_dump($event->getRequest()->request->get('facebook'));die;

        // Check token authentication availability
        if ($this->tokenStorage->getToken()) {

//            var_dump($event->getRequest()->request->all());die;
            $user = $this->tokenStorage->getToken()->getUser();

            if ($user instanceof User) {
                $facebook = $event->getRequest()->request->get('facebook');

                if($facebook > 0) {
                    $user->setFacebook($facebook);
                }
                $user->setLastloginAt(new \DateTime());
                $user->setLastActivityAt(new \DateTime());
                $this->entityManager->flush($user);
            }

        }
    }
}
