<?php

declare(strict_types = 1);

namespace App\Controller\Library;

use App\Controller\Library\SearchByName\SearchByNameFormRequest;
use App\Controller\Library\SearchByName\SearchByNameFormType;
use App\Controller\Library\SearchByTags\SearchByTagsFormRequest;
use App\Controller\Library\SearchByTags\SearchByTagsFormType;
use App\Entity\Track\TrackFacade;
use App\Entity\Track\TrackList;
use App\Spotify\Devices\PreferredDeviceProvider;
use App\Twig\FlashEnum;
use Psr\Log\LoggerInterface;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\SpotifyWebAPIException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayController extends AbstractController
{
    public function __construct(
        private SpotifyWebAPI $spotifyWebAPI,
        private PreferredDeviceProvider $preferredDeviceProvider,
        private LoggerInterface $logger,
    )
    {
    }

    #[Route(path: '/play/now', name: 'play.now')]
    public function playAction(Request $request): Response
    {
        $trackId = $request->query->get('track');

        $userDevices = $this->spotifyWebAPI->getMyDevices()->devices;
        $preferredDevice = $this->preferredDeviceProvider->getPreferredDevice($userDevices);

        if ($preferredDevice === null) {
            $this->addFlash(FlashEnum::WARNING, 'You have no active device in Spotify. Open Spotify on your phone or computer.');

            return $this->redirect($request->headers->get('referer'));
        }

        $this->spotifyWebAPI->play($preferredDevice->id, options: ['uris' => ['spotify:track:' . $trackId]]);

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/play/queue', name: 'play.queue')]
    public function queueAction(Request $request): Response
    {
        $trackIds = $request->query->all('tracks');

        try {
            foreach ($trackIds as $id) {
                $this->spotifyWebAPI->queue($id);
            }
        } catch (SpotifyWebAPIException $e) {
            if ($e->getMessage() === 'Restricted device') {
                $this->addFlash(FlashEnum::WARNING, 'You have no active device in Spotify. Open Spotify on your phone or computer.');
            } else {
                $this->logger->error('could not queue songs', ['exception' => $e]);
            }
        }

        return $this->redirect($request->headers->get('referer'));
    }

}
