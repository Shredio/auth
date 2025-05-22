<?php declare(strict_types = 1);

namespace Shredio\Auth\Context;

use Shredio\Auth\Exception\ForbiddenException;
use Shredio\Auth\Requirement\Requirement;

interface CurrentUserRequirementCheckerContext
{

	public function isSatisfied(Requirement $requirement): bool;

	/**
	 * @throws ForbiddenException
	 */
	public function require(Requirement $requirement): void;

}
