<?php

namespace App\Entity;

use App\Repository\InscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionRepository::class)]
#[ORM\Table(name: 'inscriptions')]
class Inscription
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(name: 'participants_no_participant', referencedColumnName: 'no_participant', nullable: false)]
    private Participant $participant;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(name: 'sorties_no_sortie', referencedColumnName: 'no_sortie', nullable: false)]
    private Sortie $sortie;

    #[ORM\Column(name: 'date_inscription', type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTimeInterface $dateInscription;

    public function __construct()
    {
        $this->dateInscription = new \DateTime();
    }

    public function getParticipant(): Participant
    {
        return $this->participant;
    }

    public function setParticipant(Participant $participant): static
    {
        $this->participant = $participant;
        return $this;
    }

    public function getSortie(): Sortie
    {
        return $this->sortie;
    }

    public function setSortie(Sortie $sortie): static
    {
        $this->sortie = $sortie;
        return $this;
    }

    public function getDateInscription(): \DateTimeInterface
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeInterface $dateInscription): static
    {
        $this->dateInscription = $dateInscription;
        return $this;
    }
}