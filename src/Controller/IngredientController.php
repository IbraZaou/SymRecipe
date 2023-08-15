<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class IngredientController extends AbstractController
{

    /**
     * This controller display all ingredients
     * 
     * @param IngredientRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */


    #[Route('/ingredient', name: 'ingredient.index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(IngredientRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {

        $ingredients = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients
        ]);
    }



    /**
     * This controller show a form which create an ingredient
     * 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     * 
     */


    #[Route('/ingredient/nouveau', 'ingredient.new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(
        
        Request $request,
        EntityManagerInterface $manager
        ) : Response 
        {
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();
            $ingredient->setUser($this->getUser());
            
            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre ingrédient a bien été crée !'
            );

            return $this->redirectToRoute('ingredient.index');
        }

        return $this->render('pages/ingredient/new.html.twig', [
            'form' => $form->createView()]);
    }



    /**
     * This controller allow us to edit an ingredient
     *
     * @param IngredientRepository $repository
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */


    #[Route('/ingredient/edition/{id}', 'ingredient.edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(
        IngredientRepository $repository, 
        int $id, 
        Request $request,
        EntityManagerInterface $manager,
        // a la place de @Security qui ne fonctionne plus, CurrentUser fait la faire ligne 117 à 119
        #[CurrentUser] UserInterface $currentUser) : Response
    {

        $ingredient = $repository->findOneBy(['id' => $id]);

        // Check if the current user owns the ingredient
        if($currentUser !== $ingredient->getUser()) {
            $this->addFlash(
                'warning',
                'Vous pouvez seulement modifier des ingrédients vous appartenant.'
            );

            return $this->redirectToRoute('ingredient.index');
        }

    
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();
            
            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre ingrédient a bien été modifié !'
            );

            return $this->redirectToRoute('ingredient.index');
        }

        return $this->render('pages/ingredient/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }




    /**
     * This controller allows us to delete an ingredient
     *
     * @param EntityManagerInterface $manager
     * @param IngredientRepository $repository
     * @return Response
     */



    #[Route('/ingredient/suppression/{id}', 'ingredient.delete', methods: ['GET'])]
    public function delete(
        IngredientRepository $repository, 
        int $id,
        EntityManagerInterface $manager) : Response 
    {

        $ingredient = $repository->findOneBy(['id' => $id]);
        
        $manager->remove($ingredient);
        $manager->flush();

        $this->addFlash(
            'success',
            'Votre ingrédient a bien été supprimé !'
        );

        return $this->redirectToRoute('ingredient.index');
    }

    
}