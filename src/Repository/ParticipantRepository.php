<?php

namespace App\Repository;

use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participant>
 */
class ParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    /**
     * Trouve un participant par email (mail) OU par pseudo.
     * Utilisé pour le login "email ou pseudo".
     */
    public function findOneByMailOrPseudo(string $identifier): ?Participant
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.mail = :id OR p.pseudo = :id')
            ->setParameter('id', $identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Vérifie si un participant est lié à au moins une sortie,
     * soit en tant qu'organisateur, soit en tant que participant inscrit.
     *
     * @param int $participantId Identifiant du participant
     * @return bool true si le participant est lié à au moins une sortie
     */
    public function isInAnySortie(int $participantId): bool
    {
        // 1) Organisateur d'au moins une sortie ?
        $organiseCount = (int) $this->createQueryBuilder('p')
            ->select('COUNT(s.id)')
            ->leftJoin('p.sortiesOrganisees', 's')
            ->andWhere('p.id = :id')
            ->setParameter('id', $participantId)
            ->getQuery()
            ->getSingleScalarResult();

        if ($organiseCount > 0) {
            return true;
        }

        // 2) Inscrit à au moins une sortie ?
        $inscriptionCount = (int) $this->createQueryBuilder('p')
            ->select('COUNT(i)')
            ->leftJoin('p.inscriptions', 'i')
            ->andWhere('p.id = :id')
            ->setParameter('id', $participantId)
            ->getQuery()
            ->getSingleScalarResult();

        return $inscriptionCount > 0;
    }

    /**
     * Récupère une liste de participants à partir d'un tableau d'identifiants.
     *
     * @param array $ids Tableau d'IDs de participants
     * @return array Liste des participants correspondants
     */
    public function findByIds(array $ids): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des utilisateurs selon plusieurs critères optionnels :
     * - un texte libre (pseudo, nom, prénom ou email)
     * - le statut actif (actif / inactif)
     * - le rôle administrateur (admin / non admin)
     *
     * @param string|null $q     Texte de recherche (facultatif)
     * @param string|null $actif "1" ou "0" pour filtrer les comptes actifs/inactifs
     * @param string|null $admin "1" ou "0" pour filtrer les administrateurs
     *
     * @return array Liste des utilisateurs correspondant aux critères
     */
    public function searchUsers(?string $q, ?string $actif, ?string $admin): array
    {
        $qb = $this->createQueryBuilder('p');

        if ($q) {
            $qb->andWhere('p.pseudo LIKE :q OR p.nom LIKE :q OR p.prenom LIKE :q OR p.mail LIKE :q')
                ->setParameter('q', '%'.$q.'%');
        }

        if ($actif === '1' || $actif === '0') {
            $qb->andWhere('p.actif = :actif')
                ->setParameter('actif', $actif === '1');
        }

        if ($admin === '1' || $admin === '0') {
            $qb->andWhere('p.administrateur = :admin')
                ->setParameter('admin', $admin === '1');
        }

        return $qb->orderBy('p.nom', 'ASC')
            ->addOrderBy('p.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }


}
