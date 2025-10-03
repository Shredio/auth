<?php declare(strict_types = 1);

namespace Shredio\Auth\Symfony\Context;

use Shredio\Auth\Context\CurrentUserContext;
use Shredio\Auth\Entity\UserEntity;
use Shredio\Auth\Exception\ForbiddenException;
use Shredio\Auth\Exception\LogicException;
use Shredio\Auth\Requirement\Requirement;
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

	public function getEntity(): ?UserEntity
	{
		$user = $this->security->getUser();
		if ($user === null) {
			return null;
		}

		if (!$user instanceof UserEntity) {
			throw new LogicException(sprintf('Current user %s is does not implement %s.', $user::class, UserEntity::class));
		}

		return $user;
	}

	public function isSatisfied(Requirement $requirement): bool
	{
		return $this->userRequirementChecker->isSatisfied($this->getEntity(), $requirement);
	}

	/**
	 * @throws ForbiddenException
	 */
	public function require(Requirement $requirement): void
	{
		$this->userRequirementChecker->require($this->getEntity(), $requirement);
	}

}
