<?php

namespace Wexample\SymfonyUserDemo\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Wexample\SymfonySearch\Attribute\Searchable;
use Wexample\SymfonySearch\Attribute\SearchEmail;
use Wexample\SymfonySearch\Attribute\SearchText;
use Wexample\SymfonyUser\Entity\AbstractUser;
use Wexample\SymfonyUser\Entity\Traits\UserWithNameTrait;
use Wexample\SymfonyUser\Interface\MemberInterface;
use Wexample\SymfonyUserDemo\Repository\DemoUserRepository;

/**
 * The test accounts of the demo, created from the console only. Members of
 * the demo teams, found by their address when one is added to a team.
 */
#[ORM\Entity(repositoryClass: DemoUserRepository::class)]
#[ORM\Table(name: 'demo_user')]
#[Searchable(
    fields: ['email' => new SearchEmail(), 'username' => new SearchText(), 'firstName' => new SearchText(), 'lastName' => new SearchText()],
    titleField: 'email',
)]
class DemoUser extends AbstractUser implements MemberInterface
{
    use UserWithNameTrait;

    /**
     * @var Collection<int, DemoTeamMembership>
     */
    #[ORM\OneToMany(targetEntity: DemoTeamMembership::class, mappedBy: 'user')]
    private Collection $memberships;

    public function __construct()
    {
        parent::__construct();

        $this->memberships = new ArrayCollection();
    }

    public function getMembershipLabel(): string
    {
        return (string) $this->getEmail();
    }

    public function getMemberships(): iterable
    {
        return $this->memberships;
    }

    public function addMembership(DemoTeamMembership $membership): void
    {
        $this->memberships->add($membership);
    }

    public function removeMembership(DemoTeamMembership $membership): void
    {
        $this->memberships->removeElement($membership);
    }
}
