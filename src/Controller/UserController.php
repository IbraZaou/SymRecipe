<?php

namespace App\Controller;

use App\Form\UserType;
use App\Form\UserPasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{


    /**
     *  This controller allorw us to edit user's profile
     * 
     * @param UserRepository $user
     * @param int $id
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */

    #[Route('/utilisateur/edition/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    public function edit(UserRepository $user,
    int $id,
    Request $request,
    EntityManagerInterface $manager,
    UserPasswordHasherInterface $hasher,
    #[CurrentUser] UserInterface $currentUser): Response
    
    {

        $user = $user->findOneBy(['id' => $id]);

        if($currentUser !== $user->getUser()) {
            $this->addFlash(
                'warning',
                'Vous pouvez seulement modifier votre compte.'
            );

            return $this->redirectToRoute('recipe.index');
        }

        $form = $this->createForm(UserType::class, $user);
        
        if (!$this->getUser()) {
            return $this->redirectToRoute('security.login');
        }

        if ($this->getUser() !== $user) {
            return $this->redirectToRoute('recipe.index');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


                $user = $form->getData();
                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    'Votre compte a bien été modifié !'
                );

                return $this->redirectToRoute('home.index');
        }

        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }


    /**
     * 
     * This method allows us to edit the password
     * 
     * @param UserRepository $user
     * @param int $ind
     * @param Request $request
     * @param UserPasswordHasherInterface $hasher
     * @param EntityManagerInterface $manager
     * @return Response
     * 
     */


    #[Route('/utilisateur/edition-mot-de-passe/{id}', 'user.edit.password', methods: ['GET', 'POST'])]
    public function editPassword(
        UserRepository $user,
        int $id, 
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $manager,
        #[CurrentUser] UserInterface $currentUser) : Response 
    {
        $user = $user->findOneBy(['id' => $id]);

        if($currentUser !== $user->getUser()) {
            $this->addFlash(
                'warning',
                'Vous pouvez seulement modifier le mot de passe de votre compte.'
            );
        }

        $form = $this->createForm(UserPasswordType::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            if($hasher->isPasswordValid($user, $form->getData()['plainPassword'])) {
                $user->setPassword(
                    $hasher->hashPassword(
                        $user,
                        $form->getData()['newPassword']
                    )
                );

                $manager->persist($user);
                $manager->flush();
                
                $this->addFlash(
                    'success',
                    'Le mot de passe a été modifié !'
                );

                return $this->redirectToRoute('recipe.index');
            } else {
                $this->addFlash(
                    'warning',
                    'Le mot de passe renseigné est incorrecte !'
                );
            }
        }
        
        return $this->render('pages/user/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }


    //comme la route est sur edit, tu dois passer après form 'user' => $user
    //Sinon il capte pas la variable user ici 

    
    //Delete user
    #[Route('/utilisateur/suppression/{id}', name: 'user.delete')]
    public function delete(
        UserRepository $user,
        EntityManagerInterface $manager,
        // #[CurrentUser] UserInterface $currentUser
        ): Response
    {

        $user = $this->getUser();

                $manager->remove($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    'Votre compte à bien été supprimé !'
                );

                return $this->redirectToRoute('security.logout');
        
    }

}
