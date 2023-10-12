<?php

namespace App\DataFixtures;

use App\Repository\UserRepository;
use App\Repository\RecipeRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class LikeFixtures extends Fixture
{
    public function __construct(
        private RecipeRepository $recipeRepository,
        private UserRepository $userRepository
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $users = $this->userRepository->findAll();
        $recipes = $this->recipeRepository->findAll();

        foreach ($recipes as $recipe) {
            for ($i = 0; $i < mt_rand(0, 15); $i++) {
                $recipe->addLike(
                    $users[mt_rand(0, count($users) - 1)]
                );
            }
        }

        $manager->flush();
    }
}
