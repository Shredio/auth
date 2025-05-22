<?php declare(strict_types = 1);

namespace Shredio\Auth\Entity;

use Shredio\Auth\User\User;

/**
 * @template T of User
 */
interface UserEntityProvider
{

	/**
	 * @return T|null
	 */
	public function findById(string|int $id): ?object;

}
