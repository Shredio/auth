<?php declare(strict_types = 1);

namespace Shredio\Auth;

use Shredio\Auth\Identity\UserIdentity;
use Shredio\Auth\Requirement\Requirement;

interface UserRequirementChecker
{

	public function isSatisfied(?UserIdentity $identity, Requirement $requirement): bool;

}
