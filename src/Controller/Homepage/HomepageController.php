<?php

declare(strict_types = 1);

namespace App\Controller\Homepage;

use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{

    private const VERIFIER = 'verifier';
    private const STATE = 'state';

    private const SPOTIFY_ACCESS_TOKEN = 'accessToken';
    private const SPOTIFY_REFRESH_TOKEN = 'refreshToken';

    public function __construct(
        private SpotifyWebAPI $api,
        private Session $session,
        private RequestStack $requestStack
    )
    {
    }

    /**
     * @Route("/", name="homepage")
     */
    public function homepageAction(): Response
    {
        $accessToken = $this->requestStack->getSession()->get(self::SPOTIFY_ACCESS_TOKEN);
        $refreshToken = $this->requestStack->getSession()->get(self::SPOTIFY_REFRESH_TOKEN);

        if (is_string($accessToken) === false) {
            return new Response('<body>you\'re not authorized, go to <a href="/auth">/auth</a> to authorize</body>');
        }

        $this->api->setAccessToken($accessToken);

        $playlists = $this->api->getMyPlaylists(['limit' => 50])->items;
        usort($playlists, fn($a, $b) => $a->name > $b->name);
//        $playlistToSong = [];
//
//        foreach ($playlists as $playlist) {
//            $tracks = [];
//
//            $playlistTracks = $this->api->getPlaylistTracks($playlist->id);
//
//            foreach ($playlistTracks->items as $playlistTrack){
//                $tracks[] = $playlistTrack->track;
//            }
//
//            $playlistToSong[$playlist->id.' '.$playlist->name] = $tracks;
//
//        }

        return $this->render(
            'homepage/homepage.html.twig',
            [
                'userName' => $this->api->me()->display_name,
                'userPlaylists' => $playlists,
            ]
        );
    }

    /**
     * @Route("/auth", name="auth")
     */
    public function authAction(): Response
    {
        $verifier = $this->session->generateCodeVerifier(); // Store this value somewhere, a session for example
        $challenge = $this->session->generateCodeChallenge($verifier);
        $state = $this->session->generateState();

        $this->requestStack->getSession()->set(self::VERIFIER, $verifier);
        $this->requestStack->getSession()->set(self::STATE, $state);

        $options = [
            'code_challenge' => $challenge,
            'scope' => [
                'playlist-read-private',
                'user-library-read',
                'user-read-private',
                'playlist-read-private',
                'user-read-recently-played',
                'user-read-playback-state',
            ],
            'state' => $state,
        ];

        return new RedirectResponse($this->session->getAuthorizeUrl($options));
    }

    /**
     * @Route("/callback", name="callback")
     */
    public function callbackAction(Request $request): Response
    {
        $state = $request->query->get('state');
        $authCode = $request->query->get('code');

        if ($state !== $this->requestStack->getSession()->get(self::STATE)) {
            return new Response('<body>State mismatch</body>', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->session->requestAccessToken(
            $authCode,
            $this->requestStack->getSession()->get(self::VERIFIER)
        );

        $accessToken = $this->session->getAccessToken();
        $refreshToken = $this->session->getRefreshToken();

        $this->requestStack->getSession()->set(self::SPOTIFY_ACCESS_TOKEN, $accessToken);
        $this->requestStack->getSession()->set(self::SPOTIFY_REFRESH_TOKEN, $refreshToken);

        return $this->redirectToRoute('homepage');
    }

}