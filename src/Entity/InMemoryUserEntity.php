<?php declare(strict_types = 1);

namespace Shredio\Auth\Entity;

use Deprecated;

final readonly class InMemoryUserEntity implements UserEntity
{

	/**
	 * @param non-empty-string $id
	 * @param list<string> $roles
	 */
	public function __construct(
		private string $id,
		private array $roles,
	)
	{
	}

	public function getUserIdentifier(): string
	{
		return $this->id;
	}

	public function getRoles(): array
	{
		return $this->roles;
	}

	#[Deprecated('eraseCredentials is not needed anymore')]
	public function eraseCredentials(): void
	{
		// noop
	}

}
