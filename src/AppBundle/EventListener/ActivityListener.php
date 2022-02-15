<?php

namespace AppBundle\EventListener;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Listener that updates the last activity of the authenticated user
 */
class ActivityListener
{
    protected $tokenStorage;
    protected $entityManager;

    public function __construct(TokenStorage $tokenStorage, EntityManager $entityManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
    }

    /**
     * Update the user "lastActivity" on each request
     * @param FilterControllerEvent $event
     */
    public function onCoreController(FilterControllerEvent $event)
    {
        // Check that the current request is a "MASTER_REQUEST"
        // Ignore any sub-request
        if ($event->getRequestType() !== HttpKernel::MASTER_REQUEST) {
            return;
        }

        // Check token authentication availability
        if ($this->tokenStorage->getToken()) {

            $user = $this->tokenStorage->getToken()->getUser();
            if ($user instanceof User) {

                $user->setLastRealActivityAt(new \DateTime());
                $user->setIsFrozen(false);

                if (!$user->isOnline()) {
                    $user->setLastActivityAt(new \DateTime());
                }


                if ($user && !$user->getIsActivated() && !$event->getRequest()->headers->get('version', false)) {
                    $this->entityManager->flush($user);
                    /* not activated user can get pages: activation, edit profile, edit photos, upload photo(ajax), delete photo(ajax),
                       set photo as private(ajax) */
                    $regex = '/(sign_up)|(activation)|(user\/profile\/[2-4])|(user\/profile)|(\/user\/photo\/data)|';
                    $regex .= '(user\/photo\/delete)|(user\/photo\/private)|(update\/user\/phone)|';
                    $regex .= 'api\/v[\d]\/photos/';
                    if (!(preg_match($regex, $event->getRequest()->getUri()))) {
                        $this->locate = $event->getRequest()->getLocale();
                        $this->url = $event->getRequest()->getUri();
                        $event->setController(function () {
                            return new RedirectResponse("/activation");
                        });
                    }
                }

                $this->entityManager->flush($user);
            }


        }
    }
}
