<?php 

namespace App\Controller;

use App\Model\SearchData;
use App\Form\SearchType;
use App\Repository\RecipeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController 
{

    #[Route('/', 'home.index', methods: ['GET'])]
    public function index(
        RecipeRepository $repository,
        Request $request
    ): Response
    {


        $searchData = new SearchData();

        // creation du formulaire (la barre de recherche)
        $form = $this->createForm(SearchType::class, $searchData);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            
            $searchData->page = $request->query->getInt('page', 1);
            $recipes = $repository->findBySearch($searchData);

            return $this->render('pages/recipe/index_public.html.twig', [
                'form' => $form->createView(),
                'recipes' => $recipes
            ]);
        }

        return $this->render('pages/home.html.twig', [
            'form' => $form->createView(),
            'recipes' => $repository->findPublicRecipe(3)
        ]);
    }
}