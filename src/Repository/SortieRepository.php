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

    /**
     * Recherche des sorties selon les critères de filtrage
     */
    public function findByFilters(
        ?Site $site = null,
        ?string $search = null,
        ?\DateTimeInterface $dateFrom = null,
        ?\DateTimeInterface $dateTo = null,
        bool $organisateur = false,
        bool $inscrit = false,
        bool $nonInscrit = false,
        bool $terminees = false,
        ?int $userId = null
    ): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.participants', 'p')
            ->leftJoin('s.etat', 'e');

        // Filtre par site
        if ($site) {
            $qb->andWhere('s.site = :site')
                ->setParameter('site', $site);
        }

        // Filtre par nom de sortie
        if ($search) {
            $qb->andWhere('s.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Filtre par date de début (entre dateFrom et dateTo)
        if ($dateFrom) {
            $qb->andWhere('s.dateHeureDebut >= :dateFrom')
                ->setParameter('dateFrom', $dateFrom);
        }

        if ($dateTo) {
            // Ajouter 23h59m59s pour inclure toute la journée
            $dateToEnd = (clone $dateTo)->setTime(23, 59, 59);
            $qb->andWhere('s.dateHeureDebut <= :dateTo')
                ->setParameter('dateTo', $dateToEnd);
        }

        // Filtres conditionnels (checkboxes)
        if ($userId) {
            $conditions = [];

            if ($organisateur) {
                $conditions[] = 's.organisateur = :userId';
            }

            if ($inscrit) {
                $conditions[] = 'p.id = :userId';
            }

            if ($nonInscrit) {
                // Sous-requête pour vérifier que l'utilisateur n'est PAS inscrit
                $subQb = $this->createQueryBuilder('s2')
                    ->select('1')
                    ->leftJoin('s2.participants', 'p2')
                    ->where('s2.id = s.id')
                    ->andWhere('p2.id = :userId');

                $conditions[] = $qb->expr()->not(
                    $qb->expr()->exists($subQb->getDQL())
                );
            }

            if (!empty($conditions)) {
                $qb->andWhere($qb->expr()->orX(...$conditions))
                    ->setParameter('userId', $userId);
            }
        }

        // Filtre sorties terminées
        if (!$terminees) {
            $qb->andWhere('e.libelle != :historisee')
                ->setParameter('historisee', 'Historisée');
        }

        return $qb->orderBy('s.dateHeureDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
