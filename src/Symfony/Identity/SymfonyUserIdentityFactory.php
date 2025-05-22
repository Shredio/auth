<?php declare(strict_types = 1);

namespace Shredio\Auth\Symfony\Identity;

use Shredio\Auth\Identity\UserIdentity;
use Shredio\Auth\Identity\UserIdentityFactory;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

final readonly class SymfonyUserIdentityFactory implements UserIdentityFactory
{

	public function __construct(
		private string $firewallName = 'main',
	)
	{
	}

	public function create(object $user): UserIdentity
	{
		if (!$user instanceof UserInterface) {
			throw new \LogicException(sprintf(
				'User "%s" does not implement "%s".',
				$user::class,
				UserInterface::class,
			));
		}

		return new SymfonyUserIdentity(new PostAuthenticationToken($user, $this->firewallName, $user->getRoles()));
	}

}
