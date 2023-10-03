<?php

namespace App\Repository;

use App\Entity\Parasol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Parasol>
 *
 * @method Parasol|null find($id, $lockMode = null, $lockVersion = null)
 * @method Parasol|null findOneBy(array $criteria, array $orderBy = null)
 * @method Parasol[]    findAll()
 * @method Parasol[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParasolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parasol::class);
    }

//    /**
//     * @return Parasol[] Returns an array of Parasol objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Parasol
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
