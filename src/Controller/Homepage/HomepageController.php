<?php

declare(strict_types = 1);

namespace App\Controller\Homepage;

use App\Entity\User\User;
use App\Entity\User\UserAuthenticator;
use App\Entity\User\UserRepository;
use App\Spotify\SpotifyRepository;
use App\Twig\FlashEnum;
use Psr\Log\LoggerInterface;
use SpotifyWebAPI\Session;
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

    public function __construct(
        private Session $session,
        private RequestStack $requestStack,
        private SpotifyRepository $spotifyRepository,
        private UserRepository $userRepository,
        private UserAuthenticator $userAuthenticator,
        private LoggerInterface $logger,
    )
    {
    }

    #[Route(path: '/', name: 'homepage')]
    public function homepageAction(): Response
    {
        return $this->render('homepage/homepage.html.twig',);
    }

    #[Route(path: '/logout', name: 'logout')]
    public function invalidateAuthAction(): Response
    {
        $this->requestStack->getSession()->set(SpotifyRepository::SPOTIFY_ACCESS_TOKEN, null);
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

        $this->session->requestAccessToken(
            $authCode,
            $this->requestStack->getSession()->get(self::VERIFIER)
        );

        $accessToken = $this->session->getAccessToken();
        $refreshToken = $this->session->getRefreshToken();

        $this->requestStack->getSession()->set(SpotifyRepository::SPOTIFY_ACCESS_TOKEN, $accessToken);
        $this->requestStack->getSession()->set(SpotifyRepository::SPOTIFY_REFRESH_TOKEN, $refreshToken);

        $spotifyUser = $this->spotifyRepository->getUserInfo();
        $name = $spotifyUser->id;

        $user = $this->userRepository->findByName($name);

        if ($user === null) {
            $user = new User($name);
        }

        $user->setLastLoggedInAt(new \DateTimeImmutable());
        $user->setAccessToken($accessToken);

        $this->userRepository->save($user);
        $this->userAuthenticator->authenticateUser($user);

        return $this->redirectToRoute('homepage');
    }

}
