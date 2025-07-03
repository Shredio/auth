<?php declare(strict_types = 1);

namespace Shredio\Auth\Context;

use Shredio\Auth\Exception\LogicException;
use Shredio\Auth\Exception\UnsignedUserException;
use Shredio\Auth\Identity\EntityUserIdentity;
use Shredio\Auth\Identity\UserIdentity;
use Shredio\Auth\Requirement\Requirement;
use Shredio\Auth\User\User;
use Shredio\Auth\UserRequirementChecker;

final readonly class VoterContext
{

	public function __construct(
		private Requirement $requirement,
		private ?UserIdentity $identity,
		private ?User $user,
		private UserRequirementChecker $userRequirementChecker,
	)
	{
	}

	public function getRequirement(): Requirement
	{
		return $this->requirement;
	}

	/**
	 * @param User|null $entity
	 */
	public function isRequirementSatisfiedForUserEntity(?object $entity, Requirement $requirement): bool
	{
		if ($requirement == $this->requirement) {
			throw new LogicException('Cannot check if requirement is satisfied for itself.');
		}

		return $this->userRequirementChecker->isSatisfied($entity ? new EntityUserIdentity($entity) : null, $requirement);
	}

	public function isRequirementSatisfiedForCurrentUser(Requirement $requirement): bool
	{
		if ($requirement == $this->requirement) {
			throw new LogicException('Cannot check if requirement is satisfied for itself.');
		}

		return $this->userRequirementChecker->isSatisfied($this->identity, $requirement);
	}

	public function isCurrentUserLoggedIn(): bool
	{
		return $this->user !== null;
	}

	/**
	 * @template T of User
	 * @param class-string<T> $class
	 * @return T
	 */
	public function getUser(string $class): object
	{
		if ($this->user === null) {
			throw new UnsignedUserException('User is not logged in, check isCurrentUserLoggedIn() first.');
		}

		assert($this->user instanceof $class);

		return $this->user;
	}

}
