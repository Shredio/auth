<?php declare(strict_types = 1);

namespace Tests\Common;

use Symfony\Component\Security\Core\User\UserInterface;

final readonly class User implements \Shredio\Auth\User\User, UserInterface
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
