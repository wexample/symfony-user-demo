<?php

namespace Wexample\SymfonyUserDemo\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Wexample\SymfonyOrganization\Entity\Traits\HasOrganizationMembershipsTrait;
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
    use HasOrganizationMembershipsTrait;

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

    /**
     * Its teams, and the organizations of the directory it belongs to.
     */
    public function getMemberships(): iterable
    {
        yield from $this->memberships;
        yield from $this->getOrganizationMemberships();
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
