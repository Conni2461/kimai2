<?php

/*
 * This file is part of the Kimai package.
 *
 * (c) Kevin Papst <kevin@kevinpapst.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TimesheetBundle\Voter;

use AppBundle\Entity\User;
use AppBundle\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use TimesheetBundle\Entity\Activity;

/**
 * A voter to check permissions on Activities.
 *
 * @author Kevin Papst <kevin@kevinpapst.de>
 */
class ActivityVoter extends AbstractVoter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::DELETE))) {
            return false;
        }

        if (!$subject instanceof Activity) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param Activity $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($subject, $user, $token);
            case self::EDIT:
                return $this->canEdit($subject, $user, $token);
            case self::DELETE:
                return $this->canDelete($token);
        }

        return false;
    }

    /**
     * @param Activity $activity
     * @param User $user
     * @param TokenInterface $token
     * @return bool
     */
    protected function canView(Activity $activity, User $user, TokenInterface $token)
    {
        if ($this->canEdit($activity, $user, $token)) {
            return true;
        }

        return false;
    }

    /**
     * @param Activity $activity
     * @param User $user
     * @param TokenInterface $token
     * @return bool
     */
    protected function canEdit(Activity $activity, User $user, TokenInterface $token)
    {
        if ($this->canDelete($token)) {
            return true;
        }

        return false;
    }

    /**
     * @param TokenInterface $token
     * @return bool
     */
    protected function canDelete(TokenInterface $token)
    {
        return $this->hasRole('ROLE_ADMIN', $token);
    }
}