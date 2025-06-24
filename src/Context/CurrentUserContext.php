<?php declare(strict_types = 1);

namespace Shredio\Auth\Context;

use Shredio\Auth\Identity\UserIdentity;
use Shredio\Auth\Requirement\Requirement;

interface CurrentUserContext
{

	public function getIdentity(): ?UserIdentity;

	public function isSatisfied(Requirement $requirement): bool;

}
