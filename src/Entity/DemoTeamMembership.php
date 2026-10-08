<?php

namespace Wexample\SymfonyUserDemo\Entity;

use Doctrine\ORM\Mapping as ORM;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyUser\Entity\Traits\HasGrantsTrait;
use Wexample\SymfonyUser\Interface\MemberInterface;
use Wexample\SymfonyUser\Interface\MembershipGroupInterface;
use Wexample\SymfonyUser\Interface\MembershipInterface;

/**
 * A demo account in a demo team, with its grants there.
 */
#[ORM\Entity]
#[ORM\Table(name: 'demo_user_team_membership')]
#[ORM\UniqueConstraint(columns: ['team_id', 'user_id'])]
class DemoTeamMembership extends AbstractEntity implements MembershipInterface
{
    use HasGrantsTrait;

    #[ORM\ManyToOne(inversedBy: 'memberships')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private DemoTeam $team;

    #[ORM\ManyToOne(inversedBy: 'memberships')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private DemoUser $user;

    /**
     * @param list<string> $grants
     */
    public function __construct(DemoTeam $team, DemoUser $user, array $grants)
    {
        parent::__construct();

        $this->team = $team;
        $this->user = $user;
        $this->setGrants($grants);

        $team->addMembership($this);
        $user->addMembership($this);
    }

    public function getTeam(): DemoTeam
    {
        return $this->team;
    }

    public function getUser(): DemoUser
    {
        return $this->user;
    }

    public function getMember(): MemberInterface
    {
        return $this->user;
    }

    public function getGroup(): MembershipGroupInterface
    {
        return $this->team;
    }
}
