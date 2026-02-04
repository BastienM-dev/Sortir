<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
#[ORM\Table(name: 'sorties')]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'no_sortie')]
    private ?int $id = null;

    #[ORM\Column(length: 30, nullable: false)]
    #[Assert\NotBlank(message: 'Le nom de la sortie est obligatoire')]
    #[Assert\Length(max: 30)]
    private string $nom;

    #[ORM\Column(name: 'datedebut', type: Types::DATETIME_MUTABLE, nullable: false)]
    #[Assert\NotBlank(message: 'La date de début est obligatoire')]
    private \DateTimeInterface $dateHeureDebut;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\Positive(message: 'La durée doit être positive')]
    private ?int $duree = null;

    #[ORM\Column(name: 'datecloture', type: Types::DATETIME_MUTABLE, nullable: false)]
    #[Assert\NotBlank(message: 'La date limite d\'inscription est obligatoire')]
    private \DateTimeInterface $dateLimiteInscription;

    #[ORM\Column(name: 'nbinscriptionsmax', nullable: false)]
    #[Assert\NotBlank(message: 'Le nombre maximum d\'inscriptions est obligatoire')]
    #[Assert\Positive(message: 'Le nombre maximum doit être positif')]
    private int $nbInscriptionsMax;

    #[ORM\Column(name: 'descriptioninfos', type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 500)]
    private ?string $infosSortie = null;

    #[ORM\Column(name: 'urlPhoto', length: 250, nullable: true)]
    private ?string $urlPhoto = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $motifAnnulation = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(name: 'lieux_no_lieu', referencedColumnName: 'no_lieu', nullable: false)]
    private Lieu $lieu;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(name: 'etats_no_etat', referencedColumnName: 'no_etat', nullable: false)]
    private Etat $etat;

    #[ORM\ManyToOne(inversedBy: 'sortiesOrganisees')]
    #[ORM\JoinColumn(name: 'organisateur', referencedColumnName: 'no_participant', nullable: false)]
    private Participant $organisateur;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(name: 'sites_no_site', referencedColumnName: 'no_site', nullable: false)]
    private Site $site;

    /**
     * @var Collection<int, Inscription>
     */
    #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'sortie', orphanRemoval: true)]
    private Collection $inscriptions;

    public function __construct()
    {
        $this->inscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateHeureDebut(): \DateTimeInterface
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeInterface $dateHeureDebut): static
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): \DateTimeInterface
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTimeInterface $dateLimiteInscription): static
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscriptionsMax(): int
    {
        return $this->nbInscriptionsMax;
    }

    public function setNbInscriptionsMax(int $nbInscriptionsMax): static
    {
        $this->nbInscriptionsMax = $nbInscriptionsMax;

        return $this;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(?string $infosSortie): static
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    public function getUrlPhoto(): ?string
    {
        return $this->urlPhoto;
    }

    public function setUrlPhoto(?string $urlPhoto): static
    {
        $this->urlPhoto = $urlPhoto;

        return $this;
    }

    public function getMotifAnnulation(): ?string
    {
        return $this->motifAnnulation;
    }

    public function setMotifAnnulation(?string $motifAnnulation): static
    {
        $this->motifAnnulation = $motifAnnulation;

        return $this;
    }

    public function getLieu(): Lieu
    {
        return $this->lieu;
    }

    public function setLieu(Lieu $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getEtat(): Etat
    {
        return $this->etat;
    }

    public function setEtat(Etat $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getOrganisateur(): Participant
    {
        return $this->organisateur;
    }

    public function setOrganisateur(Participant $organisateur): static
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    public function getSite(): Site
    {
        return $this->site;
    }

    public function setSite(Site $site): static
    {
        $this->site = $site;

        return $this;
    }

    /**
     * @return Collection<int, Inscription>
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(Inscription $inscription): static
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);
            $inscription->setSortie($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): static
    {
        if ($this->inscriptions->removeElement($inscription)) {
            if ($inscription->getSortie() === $this) {
                $inscription->setSortie(null);
            }
        }

        return $this;
    }

    /**
     * Retourne le nombre d'inscrits à cette sortie
     */
    public function getNbInscrits(): int
    {
        return $this->inscriptions->count();
    }

    /**
     * Vérifie si un participant est inscrit à cette sortie
     */
    public function isParticipantInscrit(Participant $participant): bool
    {
        foreach ($this->inscriptions as $inscription) {
            if ($inscription->getParticipant() === $participant) {
                return true;
            }
        }
        return false;
    }
}
