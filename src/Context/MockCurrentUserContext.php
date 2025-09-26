<?php declare(strict_types = 1);

namespace Shredio\Auth\Context;

use Shredio\Auth\Entity\UserEntity;
use Shredio\Auth\Requirement\Requirement;
use Shredio\Auth\UserRequirementChecker;

final class MockCurrentUserContext implements CurrentUserContext
{

	public const string ServiceId = 'shredio.auth.mock_current_user_context';

	private ?UserEntity $entity = null;

	public function __construct(
		private readonly CurrentUserContext $decorate,
		private readonly UserRequirementChecker $userRequirementChecker,
	)
	{
	}

	public function setEntity(?UserEntity $entity): void
	{
		$this->entity = $entity;
	}

	public function getEntity(): ?UserEntity
	{
		return $this->entity ?? $this->decorate->getEntity();
	}

	public function isSatisfied(Requirement $requirement): bool
	{
		return $this->userRequirementChecker->isSatisfied($this->getEntity(), $requirement);
	}

}
