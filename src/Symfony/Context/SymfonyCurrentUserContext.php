<?php declare(strict_types = 1);

namespace Shredio\Auth\Symfony\Context;

use Shredio\Auth\Context\CurrentUserContext;
use Shredio\Auth\Identity\UserIdentity;
use Shredio\Auth\Symfony\Identity\SymfonyUserIdentity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @template T of UserInterface
 * @implements CurrentUserContext<T>
 */
final readonly class SymfonyCurrentUserContext implements CurrentUserContext
{

	public function __construct(
		private Security $security,
	)
	{
	}

	public function getIdentity(): ?UserIdentity
	{
		$token = $this->security->getToken();

		if ($token === null) {
			return null;
		}

		return new SymfonyUserIdentity($token);
	}

	public function getEntity(): ?object
	{
		/** @var T|null */
		return $this->security->getUser();
	}

}
