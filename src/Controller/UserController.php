<?php

namespace App\Controller;

use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{


    /**
     *  This controller allorw us to edit user's profile
     * 
     * @param UserRepository $user
     * @param int $id
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */


    #[Route('/utilisateur/edition/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    public function edit(UserRepository $user,
    int $id,
    Request $request,
    EntityManagerInterface $manager): Response
    {

        $user = $user->findOneBy(['id' => $id]);
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

            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
