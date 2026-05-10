<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Menu>
 */
class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    //    /**
    //     * @return Menu[] Returns an array of Menu objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Menu
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findWithFilters(
    ?string $prixMax,
    ?string $prixMin,
    ?string $theme,
    ?string $regime,
    ?string $nbPersonnes
): array {
    $qb = $this->createQueryBuilder('m')
        ->where('m.actif = true');

    if ($prixMax) {
        $qb->andWhere('m.prix <= :prixMax')
           ->setParameter('prixMax', $prixMax);
    }
    if ($prixMin) {
        $qb->andWhere('m.prix >= :prixMin')
           ->setParameter('prixMin', $prixMin);
    }
    if ($theme) {
        $qb->andWhere('m.theme = :theme')
           ->setParameter('theme', $theme);
    }
    if ($regime) {
        $qb->andWhere('m.regime = :regime')
           ->setParameter('regime', $regime);
    }
    if ($nbPersonnes) {
        $qb->andWhere('m.nb_personnes_min <= :nbPersonnes')
           ->setParameter('nbPersonnes', $nbPersonnes);
    }

    return $qb->getQuery()->getResult();
}
}
