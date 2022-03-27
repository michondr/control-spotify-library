<?php

declare(strict_types = 1);

namespace App\Controller\Library;

use App\Controller\Library\SearchByName\SearchByNameFormRequest;
use App\Controller\Library\SearchByName\SearchByNameFormType;
use App\Controller\Library\SearchByTags\SearchByTagsFormRequest;
use App\Controller\Library\SearchByTags\SearchByTagsFormType;
use App\Entity\Track\TrackFacade;
use App\Entity\Track\TrackList;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private TrackFacade $trackFacade
    )
    {
    }

    #[Route(path: '/search', name: 'search')]
    public function searchAction(Request $request): Response
    {
        $resultTrackList = new TrackList();

        $searchByNameData = new SearchByNameFormRequest();
        $searchByNameForm = $this->formFactory->create(SearchByNameFormType::class, $searchByNameData);
        $searchByNameForm->handleRequest($request);

        if ($searchByNameForm->isSubmitted() && $searchByNameForm->isValid()) {
            $resultTrackList = $this->trackFacade->getTrackListByNameMatchingQuery($searchByNameData->getQuery());
        }

        $searchByTagsData = new SearchByTagsFormRequest();
        $searchByTagsForm = $this->formFactory->create(SearchByTagsFormType::class, $searchByTagsData);
        $searchByTagsForm->handleRequest($request);

        if ($searchByTagsForm->isSubmitted() && $searchByTagsForm->isValid()) {
            $resultTrackList = $this->trackFacade->getTrackListByTags($searchByTagsData->getTags());
        }

        return $this->render('library/search.html.twig', [
            'searchByNameForm' => $searchByNameForm->createView(),
            'searchByTagsForm' => $searchByTagsForm->createView(),
            'resultTrackList' => $resultTrackList,
        ]);
    }

}
