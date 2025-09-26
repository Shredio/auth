<?php declare(strict_types = 1);

namespace Shredio\Auth\Context;

use Shredio\Auth\Entity\UserEntity;
use Shredio\Auth\Requirement\Requirement;

interface CurrentUserContext
{

	public function getEntity(): ?UserEntity;

	public function isSatisfied(Requirement $requirement): bool;

}
