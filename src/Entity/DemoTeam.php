<?php

namespace Wexample\SymfonyUserDemo\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyUser\Interface\MembershipGroupInterface;

/**
 * A team of the demo, which the demo accounts belong to.
 */
#[ORM\Entity]
#[ORM\Table(name: 'demo_user_team')]
class DemoTeam extends AbstractEntity implements MembershipGroupInterface
{
    /**
     * @var Collection<int, DemoTeamMembership>
     */
    #[ORM\OneToMany(targetEntity: DemoTeamMembership::class, mappedBy: 'team', cascade: ['remove'])]
    private Collection $memberships;

    public function __construct(
        #[ORM\Column(length: 100)]
        private string $name,
    ) {
        parent::__construct();

        $this->memberships = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMembershipLabel(): string
    {
        return $this->name;
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
