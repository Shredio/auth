<?php declare(strict_types = 1);

namespace Shredio\Auth\Symfony\Context;

use Shredio\Auth\Context\CurrentUserContext;
use Shredio\Auth\Context\CurrentUserRequirementCheckerContext;
use Shredio\Auth\Exception\ForbiddenException;
use Shredio\Auth\Requirement\Requirement;
use Shredio\Auth\UserRequirementChecker;

final readonly class SymfonyCurrentUserRequirementCheckerContext implements CurrentUserRequirementCheckerContext
{

	/**
	 * @param CurrentUserContext<object> $currentUserContext
	 */
	public function __construct(
		private CurrentUserContext $currentUserContext,
		private UserRequirementChecker $userRequirementChecker,
	)
	{
	}

	public function isSatisfied(Requirement $requirement): bool
	{
		return $this->userRequirementChecker->isSatisfied($this->currentUserContext->getIdentity(), $requirement);
	}

	/**
	 * @throws ForbiddenException
	 */
	public function require(Requirement $requirement): void
	{
		if ($this->isSatisfied($requirement)) {
			return;
		}

		throw new ForbiddenException($this->currentUserContext->getIdentity()?->getId(), $requirement);
	}

}
