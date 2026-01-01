<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;
use Symfony\Component\Translation\LocaleSwitcher;

class LocaleListener implements EventSubscriberInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private LocaleSwitcher $localeSwitcher
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest()) {
            return;
        }

        $locale = null;

        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser() instanceof User) {
            /** @var User $user */
            $user = $token->getUser();
            $locale = $user->getLocale();
        } else {
            // Check session for locale if not logged in
            if ($request->hasSession() && $sLocale = $request->getSession()->get('_locale')) {
                $locale = $sLocale;
            }
        }

        if ($locale) {
            $this->localeSwitcher->setLocale($locale);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
                // must be registered AFTER the security listener (8)
            KernelEvents::REQUEST => [['onKernelRequest', 7]],
        ];
    }
}
