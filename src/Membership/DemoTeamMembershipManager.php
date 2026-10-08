<?php

namespace Wexample\SymfonyUserDemo\Membership;

use Wexample\SymfonyUser\Interface\MemberInterface;
use Wexample\SymfonyUser\Interface\MembershipGroupInterface;
use Wexample\SymfonyUser\Interface\MembershipInterface;
use Wexample\SymfonyUser\Interface\MembershipManagerInterface;
use Wexample\SymfonyUserDemo\Entity\DemoTeam;
use Wexample\SymfonyUserDemo\Entity\DemoTeamMembership;
use Wexample\SymfonyUserDemo\Entity\DemoUser;

/**
 * The demo teams, for the members screens of symfony-user-ds:
 * `/memberships/team:{id}`.
 */
class DemoTeamMembershipManager implements MembershipManagerInterface
{
    public static function getName(): string
    {
        return 'team';
    }

    public function getGroupClass(): string
    {
        return DemoTeam::class;
    }

    public function getMemberClass(): string
    {
        return DemoUser::class;
    }

    public function createMembership(MembershipGroupInterface $group, MemberInterface $member, array $grants): MembershipInterface
    {
        return new DemoTeamMembership($group, $member, $grants);
    }

    public function removeMembership(MembershipInterface $membership): void
    {
        $membership->getTeam()->removeMembership($membership);
        $membership->getUser()->removeMembership($membership);
    }
}
