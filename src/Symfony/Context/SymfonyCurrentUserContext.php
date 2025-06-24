<?php declare(strict_types = 1);

namespace Shredio\Auth\Symfony\Context;

use Shredio\Auth\Context\CurrentUserContext;
use Shredio\Auth\Identity\UserIdentity;
use Shredio\Auth\Requirement\Requirement;
use Shredio\Auth\Symfony\Identity\SymfonyUserIdentity;
use Shredio\Auth\UserRequirementChecker;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class SymfonyCurrentUserContext implements CurrentUserContext
{

	public function __construct(
		private Security $security,
		private UserRequirementChecker $userRequirementChecker,
	)
	{
	}

	public function getIdentity(): ?UserIdentity
	{
		$token = $this->security->getToken();

		if ($token === null) {
			return null;
		}

		return new SymfonyUserIdentity($token);
	}

	public function isSatisfied(Requirement $requirement): bool
	{
		return $this->userRequirementChecker->isSatisfied($this->getIdentity(), $requirement);
	}

}
