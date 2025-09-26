<?php declare(strict_types = 1);

namespace Shredio\Auth\Context;

use Shredio\Auth\Exception\LogicException;
use Shredio\Auth\Exception\UnsignedUserException;
use Shredio\Auth\Entity\UserEntity;
use Shredio\Auth\Requirement\Requirement;
use Shredio\Auth\UserRequirementChecker;

final readonly class VoterContext
{

	public function __construct(
		private Requirement $requirement,
		private ?UserEntity $entity,
		private UserRequirementChecker $userRequirementChecker,
	)
	{
	}

	public function getRequirement(): Requirement
	{
		return $this->requirement;
	}

	public function isRequirementSatisfiedForUserEntity(?UserEntity $entity, Requirement $requirement): bool
	{
		if ($requirement == $this->requirement) {
			throw new LogicException('Cannot check if requirement is satisfied for itself.');
		}

		return $this->userRequirementChecker->isSatisfied($entity, $requirement);
	}

	public function isRequirementSatisfiedForCurrentUser(Requirement $requirement): bool
	{
		if ($requirement == $this->requirement) { // == is intentional
			throw new LogicException('Cannot check if requirement is satisfied for itself.');
		}

		return $this->userRequirementChecker->isSatisfied($this->entity, $requirement);
	}

	public function isCurrentUserLoggedIn(): bool
	{
		return $this->entity !== null;
	}

	/**
	 * @template T of UserEntity
	 * @param class-string<T> $class
	 * @return T
	 */
	public function getUser(string $class): object
	{
		if ($this->entity === null) {
			throw new UnsignedUserException('User is not logged in, check isCurrentUserLoggedIn() first.');
		}

		assert($this->entity instanceof $class);
		return $this->entity;
	}

}
