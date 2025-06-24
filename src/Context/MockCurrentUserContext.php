<?php declare(strict_types = 1);

namespace Shredio\Auth\Context;

use Shredio\Auth\Identity\UserIdentity;
use Shredio\Auth\Requirement\Requirement;
use Shredio\Auth\UserRequirementChecker;

final class MockCurrentUserContext implements CurrentUserContext
{

	public const string ServiceId = 'shredio.auth.mock_current_user_context';

	private ?UserIdentity $identity = null;

	public function __construct(
		private readonly CurrentUserContext $decorate,
		private readonly UserRequirementChecker $userRequirementChecker,
	)
	{
	}

	public function setIdentity(?UserIdentity $identity): void
	{
		$this->identity = $identity;
	}

	public function getIdentity(): ?UserIdentity
	{
		return $this->identity ?? $this->decorate->getIdentity();
	}

	public function isSatisfied(Requirement $requirement): bool
	{
		return $this->userRequirementChecker->isSatisfied($this->getIdentity(), $requirement);
	}

}
