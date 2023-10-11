<?php

namespace App\Repository;

use App\Model\SearchData;
use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;



/**
 * @extends ServiceEntityRepository<Recipe>
 *
 * @method Recipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recipe[]    findAll()
 * @method Recipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginatorInterface)
    {
        parent::__construct($registry, Recipe::class);
    }

    /**
     * This method allow us to find public recipes based on number recipes
     * 
     * @param integer $nbRecipes
     * @return array
     */

    public function findPublicRecipe(?int $nbRecipes): array
    {
        $queryBuilder = $this->createQueryBuilder('r')
        ->where('r.isPublic = 1')
        ->orderBy('r.createdAt', 'DESC');
    
        if(!$nbRecipes !== 0 || !$nbRecipes !== null) {
            $queryBuilder->setMaxResults($nbRecipes);
        }

        return $queryBuilder->getQuery()
        ->getResult();
    }



    public function findBySearch(SearchData $searchData): PaginationInterface
    {
        $data = $this->createQueryBuilder('r');

        if(!empty($searchData->q)) {
            $data = $data
                ->andWhere('r.name LIKE :r')
                ->setParameter('r', "%{$searchData->q}%");
        }

        $data = $data
            ->getQuery()
            ->getResult();

        $recipes = $this->paginatorInterface->paginate($data, $searchData->page, 9);

        return $recipes;
    }

}
