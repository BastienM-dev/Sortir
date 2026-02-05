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
}
