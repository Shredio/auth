<?php declare(strict_types = 1);

namespace Shredio\Auth\Symfony\Identity;

use Shredio\Auth\Identity\UserIdentity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final readonly class SymfonyUserIdentity implements UserIdentity
{

	public function __construct(
		public TokenInterface $token,
	)
	{
	}

	public function getId(): string
	{
		return $this->token->getUserIdentifier();
	}

	public function getRoles(): array
	{
		/** @var list<string> */
		return $this->token->getRoleNames();
	}

}
