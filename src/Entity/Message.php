<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateEnvoi = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $expediteur = null;

    #[ORM\ManyToOne(inversedBy: 'messageRecu')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $destinataire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getDateEnvoi(): ?\DateTimeInterface
    {
        return $this->dateEnvoi;
    }

    public function setDateEnvoi(\DateTimeInterface $dateEnvoi): static
    {
        $this->dateEnvoi = $dateEnvoi;

        return $this;
    }

    // public function getExpediteur(): ?Utilisateur
    // {
    //     return $this->expediteur;
    // }

    // public function setExpediteur(?Utilisateur $expediteur): static
    // {
    //     $this->expediteur = $expediteur;

    //     return $this;
    // }

    // public function getDestinataire(): ?Utilisateur
    // {
    //     return $this->destinataire;
    // }

    // public function setDestinataire(?Utilisateur $destinataire): static
    // {
    //     $this->destinataire = $destinataire;

    //     return $this;
    // }


    public function getExpediteur(): ?User
    {
        return $this->expediteur;
    }

    public function setExpediteur(?User $expediteur): static
    {
        $this->expediteur = $expediteur;

        return $this;
    }

    public function getDestinataire(): ?User
    {
        return $this->destinataire;
    }

    public function setDestinataire(?User $destinataire): static
    {
        $this->destinataire = $destinataire;

        return $this;
    }

    public function __toString()
    {
        return $this->contenu.' (Envoyé par :'.$this->getExpediteur()->getNom().')' ;
    }
}
