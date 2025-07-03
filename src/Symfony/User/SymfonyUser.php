<?php declare(strict_types = 1);

namespace Shredio\Auth\Symfony\User;

use Shredio\Auth\User\User;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class SymfonyUser implements User
{

	public function __construct(
		private UserInterface $user,
	)
	{
	}

	public function getUserIdAsString(): string
	{
		return $this->user->getUserIdentifier();
	}

	public function getRoles(): array
	{
		/** @var list<string> */
		return $this->user->getRoles();
	}

}
