<?php

namespace App\Controller;

use App\Entity\Recipe;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LikeController extends AbstractController
{
    #[Route('/like/recette/{id}', name: 'like.recipe', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function like(Recipe $recipe, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();

        if ($recipe->isLikedByUser($user)) {
            $recipe->removeLike($user);
            $manager->flush();

            return $this->json([
                'message' => 'Le like a été supprimé.',
                'nbLike' => $recipe->howManyLikes()
            ]);
        }

        $recipe->addLike($user);
        $manager->flush();

        return $this->json([
            'message' => 'Le like a été ajouté.',
            'nbLike' => $recipe->howManyLikes()
        ]);
    }
}
