<?php declare(strict_types = 1);

namespace Shredio\Auth\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserEntity extends UserInterface
{

	/**
	 * @return non-empty-string
	 */
	public function getUserIdentifier(): string;

	/**
	 * @return list<string>
	 */
	public function getRoles(): array;

}
