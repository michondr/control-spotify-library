<?php

declare(strict_types = 1);

namespace App\Controller\Library;

use App\Spotify\SpotifyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LibraryController extends AbstractController
{

    public function __construct(
    )
    {
    }

    #[Route(path: '/library', name: 'library')]
    public function homepageAction(): Response
    {
        return $this->render(
            'library/library.html.twig',
            [

            ]
        );
    }

}
