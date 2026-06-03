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

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column]
    private ?bool $active = null;

    /**
     * @var Collection<int, SystemLog>
     */
    #[ORM\OneToMany(targetEntity: SystemLog::class, mappedBy: 'uset')]
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

    public function getAtivo(): ?string
    {
        return $this->ativo;
    }

    public function setAtivo(string $ativo): static
    {
        $this->ativo = $ativo;

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
            $systemLog->setUset($this);
        }

        return $this;
    }

    public function removeSystemLog(SystemLog $systemLog): static
    {
        if ($this->systemLogs->removeElement($systemLog)) {
            // set the owning side to null (unless already changed)
            if ($systemLog->getUset() === $this) {
                $systemLog->setUset(null);
            }
        }

        return $this;
    }
}
