<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
class Usuario
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nome = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $senha = null;

    #[ORM\Column]
    private ?bool $ativo = false;

    #[ORM\Column(length: 50)]
    private ?string $perfil = null;

    /**
     * @var Collection<int, SystemLog>
     */
    #[ORM\OneToMany(targetEntity: SystemLog::class, mappedBy: 'usuario')]
    private Collection $systemLogs;

    public function __construct()
    {
        $this->systemLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): static
    {
        $this->nome = $nome;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getSenha(): ?string
    {
        return $this->senha;
    }

    public function setSenha(string $senha): static
    {
        $this->senha = $senha;

        return $this;
    }

    public function isAtivo(): ?bool
    {
        return $this->ativo;
    }

    public function setAtivo(bool $ativo): static
    {
        $this->ativo = $ativo;

        return $this;
    }

    public function getPerfil(): ?string
    {
        return $this->perfil;
    }

    public function setPerfil(string $perfil): static
    {
        $this->perfil = $perfil;

        return $this;
    }

    /**
     * @return Collection<int, SystemLog>
     */
    public function getSystemLogs(): Collection
    {
        return $this->systemLogs;
    }

    public function addSystemLog(SystemLog $systemLog): static
    {
        if (!$this->systemLogs->contains($systemLog)) {
            $this->systemLogs->add($systemLog);
            $systemLog->setUsuario($this);
        }

        return $this;
    }

    public function removeSystemLog(SystemLog $systemLog): static
    {
        if ($this->systemLogs->removeElement($systemLog)) {
            if ($systemLog->getUsuario() === $this) {
                $systemLog->setUsuario(null);
            }
        }

        return $this;
    }
}