<?php

namespace App\Controller;

use App\Entity\Mark;
use App\Entity\Recipe;
use App\Entity\Category;
use App\Form\MarkType;
use App\Form\RecipeType;
use App\Repository\MarkRepository;
use App\Repository\RecipeRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;



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


    #[Route('/recette/publique', 'recipe.index.public', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function indexPublic(
        PaginatorInterface $paginator,
        RecipeRepository $repository,
        Request $request
    ): Response{

        $cache = new FilesystemAdapter();
        $data = $cache->get('recipes', function (ItemInterface $item) use ($repository) {
            $item->expiresAfter(15);
            return $repository->findPublicRecipe(null);
        });

        $recipes = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            10
        );


        return $this->render('pages/recipe/index_public.html.twig', [
            'recipes' => $recipes
        ]);
    }


    /**
     * This controller allows us to see a recipe if this one is public
     * 
     * @param Recipe $recipe
     * @param AuthorizationCheckerInterface $authorizationChecker
     *  @return Response
     */

    
    #[Route('/recette/{id}', 'recipe.show', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function show(
    Recipe $recipe,
    AuthorizationCheckerInterface $authorizationChecker,
    Request $request,
    MarkRepository $markRepository,
    EntityManagerInterface $manager) : Response
    {
        $mark = new Mark();
        $form = $this->createForm(MarkType::class, $mark);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $mark->setUser($this->getUser())
                ->setRecipe($recipe);

            //ne pas noté 2 fois la meme recette
            $existingMark = $markRepository->findOneBy([
                'user' => $this->getUser(),
                'recipe' => $recipe
            ]);

            if(!$existingMark) {
                $manager->persist($mark);
            }else{
                $existingMark->setMark(
                    $form->getData()->getMark()
                );
            }

            // Enregistrement des modifications dans la base de données
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre note a bien été prise en compte.'
            );

            return $this->redirectToRoute('recipe.show', ['id' => $recipe->getId()]);
        }

        //Cela va permettre au User de ne pas pouvoir acceder au recette non public "isPublic = 0"
        if (!$authorizationChecker->isGranted('ROLE_USER') || (!$recipe->isIsPublic() && $this->getUser() !== $recipe->getUser())) {
            throw new AccessDeniedException('Access denied');
        }

        return $this->render('pages/recipe/show.html.twig', [
            'recipe' => $recipe,
            'form' => $form->createView()
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


    #[ROUTE('/recette/creation', 'recipe.new', methods: ['GET', 'POST'],  priority: 1)]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request $request,
        EntityManagerInterface $manager,
        CategoryRepository $categoryRepository
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

            return $this->render('pages/recipe/new.html.twig', [
                'form' => $form->createView()
            ]);
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
