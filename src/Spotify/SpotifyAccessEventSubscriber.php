<?php

declare(strict_types = 1);

namespace App\Spotify;

use App\Spotify\Exception\SpotifyNeedsAuthorizationException;
use SpotifyWebAPI\SpotifyWebAPIException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SpotifyAccessEventSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private SpotifyRepository $spotifyRepository,
        private UrlGeneratorInterface $urlGenerator
    )
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['validateSpotifyAccess', 0],
            ],
        ];
    }

    public function validateSpotifyAccess(RequestEvent $event)
    {
        $request = $event->getRequest();

        $isMainContext = $request->attributes->get('_firewall_context') === 'security.firewall.map.context.main';
        $isHomepage = str_starts_with($request->attributes->get('_controller'), 'App\Controller\Homepage\HomepageController');

        if ($isMainContext && $isHomepage === false) {
            try {
                $this->spotifyRepository->getUserInfo();
            } catch (SpotifyNeedsAuthorizationException) {
                $event->setResponse(new RedirectResponse($this->urlGenerator->generate('auth')));
            } catch (SpotifyWebAPIException $e) {
                if ($e->getMessage() === 'The access token expired') {
                    $event->setResponse(new RedirectResponse($this->urlGenerator->generate('auth')));
                }
                if($e->getMessage() === 'User not registered in the Developer Dashboard'){
                    $event->setResponse(new RedirectResponse($this->urlGenerator->generate('homepage')));
                }
            }
        }
    }
}