<?php declare(strict_types = 1);

namespace Shredio\Auth\Context;

use Shredio\Auth\Entity\UserEntity;
use Shredio\Auth\Requirement\Requirement;

final readonly class StaticCurrentUserContext implements CurrentUserContext
{

	/** @var callable(Requirement $requirement): bool */
	private mixed $isSatisfied;

	/**
	 * @param ?callable(Requirement $requirement): bool $isSatisfied
	 */
	public function __construct(
		private ?UserEntity $userEntity = null,
		?callable $isSatisfied = null,
	)
	{
		$this->isSatisfied = $isSatisfied ?? fn (Requirement $requirement): true => true;
	}

	public function getEntity(): ?UserEntity
	{
		return $this->userEntity;
	}

	public function isSatisfied(Requirement $requirement): bool
	{
		return ($this->isSatisfied)($requirement);
	}

}
