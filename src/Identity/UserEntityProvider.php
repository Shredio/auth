<?php declare(strict_types = 1);

namespace Shredio\Auth\Identity;

/**
 * @template T of object
 */
interface UserEntityProvider
{

	/**
	 * @return T|null
	 */
	public function getUserEntity(string|int $id): ?object;

}
