<?php declare(strict_types = 1);

namespace Tests\Common;

use Shredio\Auth\Entity\UserEntityProvider;

/**
 * @implements UserEntityProvider<User>
 */
final class InMemoryUserEntityProvider implements UserEntityProvider
{

	private ?User $user = null;

	public function setUser(?User $user): void
	{
		$this->user = $user;
	}

	public function findById(int|string $id): ?User
	{
		return $this->user;
	}

}
