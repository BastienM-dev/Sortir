<?php

namespace App\Repository;

use App\Entity\Site;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findAllSortiesBySite(Site $site): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.site = :site')
            ->setParameter('site', $site)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * Récupère les sorties à archiver :
     * - date de début antérieure à la limite
     * - état différent de "Historisée"
     */
    public function findToArchive(\DateTimeImmutable $limit): array
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.etat', 'e')
            ->andWhere('s.dateHeureDebut < :limit')
            ->andWhere('e.libelle != :hist')
            ->setParameter('limit', $limit)
            ->setParameter('hist', 'Historisée')
            ->getQuery()
            ->getResult();
    }
}
