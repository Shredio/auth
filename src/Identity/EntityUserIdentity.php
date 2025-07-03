<?php declare(strict_types = 1);

namespace Shredio\Auth\Identity;

use Shredio\Auth\User\User;

/**
 * @template T of User
 */
final readonly class EntityUserIdentity implements UserIdentity
{

	/**
	 * @param T $entity
	 */
	public function __construct(
		private User $entity,
	)
	{
	}

	public function getId(): string
	{
		return $this->entity->getUserIdAsString();
	}

	/**
	 * @return T
	 */
	public function getEntity(): User
	{
		return $this->entity;
	}

	/**
	 * @return list<string>
	 */
	public function getRoles(): array
	{
		return $this->entity->getRoles();
	}

}
