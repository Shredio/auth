<?php declare(strict_types = 1);

namespace Tests\Common;

use Shredio\Auth\Entity\UserEntity;

final readonly class User implements UserEntity
{

	public function __construct(
		public int $id,
		public string $role = 'ROLE_USER',
	)
	{
	}

	public function getUserIdAsString(): string
	{
		return (string) $this->id;
	}

	public function getUserIdentifier(): string
	{
		return (string) $this->id;
	}

	public function getRoles(): array
	{
		return [$this->role];
	}

	public function eraseCredentials(): void
	{
	}

}
