<?php

declare(strict_types = 1);

namespace App\Controller\Homepage;

use App\Entity\Tag\TagFacade;
use App\Entity\User\User;
use App\Entity\User\UserAuthenticator;
use App\Entity\User\UserRepository;
use App\Spotify\Exception\SpotifyNeedsAuthorizationException;
use App\Spotify\SpotifyRepository;
use App\Twig\FlashEnum;
use Psr\Log\LoggerInterface;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPIAuthException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class HomepageController extends AbstractController
{
    public const SPOTIFY_ACCESS_TOKEN = 'accessToken';
    public const SPOTIFY_REFRESH_TOKEN = 'refreshToken';

    private const VERIFIER = 'verifier';
    private const STATE = 'state';

    public function __construct(
        private Session $session,
        private RequestStack $requestStack,
        private SpotifyRepository $spotifyRepository,
        private UserRepository $userRepository,
        private UserAuthenticator $userAuthenticator,
        private TagFacade $tagFacade,
    )
    {
    }

    #[Route(path: '/', name: 'homepage')]
    public function homepageAction(): Response
    {
        return $this->render('homepage/homepage.html.twig',);
    }

    #[Route(path: '/not-registered-in-dev-mode', name: 'no_dev_mode')]
    public function noDevModeAction(): Response
    {
        return $this->render('homepage/noDevMode.html.twig',);
    }

    #[Route(path: '/logout', name: 'logout')]
    public function invalidateAuthAction(): Response
    {
        $this->requestStack->getSession()->set(self::SPOTIFY_ACCESS_TOKEN, null);
        $this->userAuthenticator->logoutUser();

        return $this->redirectToRoute('homepage');
    }

    #[Route(path: '/auth', name: 'auth')]
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
                'user-read-private',
                'playlist-read-private',
                'user-read-playback-state',
                'user-modify-playback-state',
            ],
            'state' => $state,
        ];

        return new RedirectResponse($this->session->getAuthorizeUrl($options));
    }

    #[Route(path: '/callback', name: 'callback')]
    public function callbackAction(Request $request): Response
    {
        $state = $request->query->get('state');
        $authCode = $request->query->get('code');
        $error = $request->query->get('error');

        if ($error !== null) {
            $this->addFlash(FlashEnum::DANGER, 'Authentication failed');

            return $this->redirectToRoute('homepage');
        }

        if ($state !== $this->requestStack->getSession()->get(self::STATE)) {
            return new Response('<body>State mismatch</body>', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            $this->session->requestAccessToken(
                $authCode,
                $this->requestStack->getSession()->get(self::VERIFIER)
            );
        } catch (SpotifyWebAPIAuthException $e) {
            $this->addFlash(FlashEnum::DANGER, sprintf('Authentication failed, please try again (%s)', $e->getMessage()));

            return $this->redirectToRoute('homepage');
        }

        $accessToken = $this->session->getAccessToken();
        $refreshToken = $this->session->getRefreshToken();

        $this->requestStack->getSession()->set(self::SPOTIFY_ACCESS_TOKEN, $accessToken);
        $this->requestStack->getSession()->set(self::SPOTIFY_REFRESH_TOKEN, $refreshToken);

        $spotifyUser = $this->spotifyRepository->getUserInfo($accessToken);
        $name = $spotifyUser->id;

        $user = $this->userRepository->findByName($name);

        if ($user === null) {
            $user = new User($name);
            $response = $this->redirectToRoute('load_user_library');
        } else {
            $response = $this->redirectToRoute('homepage');
        }

        $user->setLastLoggedInAt(new \DateTimeImmutable());
        $user->setAccessToken($accessToken);

        $this->userRepository->save($user);
        $this->userAuthenticator->authenticateUser($user);

        return $response;
    }

    #[Route(path: '/load-user-library', name: 'load_user_library')]
    public function loadUserLibraryAction(Request $request): Response
    {
        try {
            $userPlaylists = $this->spotifyRepository->getPlaylists();
        } catch (SpotifyNeedsAuthorizationException) {
            $this->addFlash(FlashEnum::WARNING, 'You must be logged in for this action');

            return $this->redirectToRoute('homepage');
        }

        $this->tagFacade->createFreshFromPlaylists($userPlaylists);

        $process = Process::fromShellCommandline('bin/console csl:tag:load-by-playlists-for-user $USERNAME', '../');
        $process->start(env: ['USERNAME' => $this->getUser()->getUserIdentifier()]);

        $message = sprintf('All %d tags successfully created from playlists, your library will be fetched shorthly', count($userPlaylists));
        $this->addFlash(FlashEnum::SUCCESS, $message);

        return $this->redirectToRoute('homepage');
    }

}
