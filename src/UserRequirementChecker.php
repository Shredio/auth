<?php declare(strict_types = 1);

namespace Shredio\Auth;

use Shredio\Auth\Entity\UserEntity;
use Shredio\Auth\Exception\ForbiddenException;
use Shredio\Auth\Requirement\Requirement;

interface UserRequirementChecker
{

	public function isSatisfied(?UserEntity $entity, Requirement $requirement): bool;

	/**
	 * @throws ForbiddenException
	 */
	public function require(?UserEntity $entity, Requirement $requirement): void;

}
