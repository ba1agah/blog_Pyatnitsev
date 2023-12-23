<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\SearchDto;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use App\Form\AppSearchType;
use App\Service\ArticleServiceInterface;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Pagerfanta;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private const RECENT_ARTICLE_COUNT_ON_HOME = 5;
    #[Route('/', name: 'homepage')]
    public function index(ArticleServiceInterface $articleService, Request $request): Response
    {
        $searchDto = new SearchDto();
        $form = $this->createForm(AppSearchType::class, $searchDto);

        $form -> handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $query = $articleService->getRecentArticles(self::RECENT_ARTICLE_COUNT_ON_HOME, $searchDto->getSearch());
        } else {
            $query = $articleService->getRecentArticles(self::RECENT_ARTICLE_COUNT_ON_HOME);
        }

        $pagerfanta = new Pagerfanta(
            new QueryAdapter($query)
        );

        $pagerfanta->setMaxPerPage(2);

        if ($request->query->has('page')){
            $pagerfanta->setCurrentPage((int)$request->query->get('page',1));
        }

        return $this->render('home/index.html.twig', [
            'articles' => $pagerfanta,
            'form' => $form,
        ]);
    }
}
