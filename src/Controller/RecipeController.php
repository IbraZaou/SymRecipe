<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class RecipeController extends AbstractController
{

    /**
     * 
     * This controller display all the recipes
     * 
     * @param RecipeRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     * 
     * 
     */

    #[Route('/recette', name: 'recipe.index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(
        RecipeRepository $repository, 
        PaginatorInterface $paginator, 
        Request $request 
    ): Response {

        $recipes = $paginator->paginate(
                    $repository->findBy(['user' => $this->getUser()]),
                    $request->query->getInt('page', 1),
                    10
        );

        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes
        ]);
    }


/**
 * This method creates a recipe with a form 
 * 
 * @param Request $request
 * @param EntityManagerInterface $manager
 * @return Response
 * 
 */


    #[ROUTE('/recette/creation', 'recipe.new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request $request,
        EntityManagerInterface $manager,
    ) : Response {

            $recipe = new Recipe();
            $form = $this->createForm(RecipeType::class, $recipe);

            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()) {
                $recipe = $form->getData();
                $recipe->setUser($this->getUser());

                $manager->persist($recipe);
                $manager->flush();

                $this->addFlash(
                    'success',
                    'Votre recette a bien été crée !'
                );

                return $this->redirectToRoute('recipe.index');
            }

            return $this->render('pages/recipe/new.html.twig', ['form' => $form->createView()]);
    }


    /**
     * This controller allow us to edit an ingredient
     *
     * @param RecipeRepository $repository
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */




    #[Route('/recette/edition/{id}', 'recipe.edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(
        RecipeRepository $recipe, 
        int $id, 
        Request $request,
        EntityManagerInterface $manager,
        #[CurrentUser] UserInterface $currentUser) : Response
    {

        $recipe = $recipe->findOneBy(['id' => $id]);

        if($currentUser !== $recipe->getUser()) {
            $this->addFlash(
                'warning',
                'Vous pouvez seulement modifier des recettes vous appartenant.'
            );

            return $this->redirectToRoute('recipe.index');
        }

        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            
            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre recette a bien été modifié !'
            );

            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('pages/recipe/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }



    /**
     * This controller allows us to delete a recipe
     *
     * @param EntityManagerInterface $manager
     * @param RecipeRepository $repository
     * @return Response
     */



    #[Route('/recette/suppression/{id}', 'recipe.delete', methods: ['GET'])]
    public function delete(
        RecipeRepository $repository, 
        int $id,
        EntityManagerInterface $manager) : Response 
    {

        $recipe = $repository->findOneBy(['id' => $id]);
        
        $manager->remove($recipe);
        $manager->flush();

        $this->addFlash(
            'success',
            'Votre recette a bien été supprimé !'
        );

        return $this->redirectToRoute('recipe.index');
    }

}
