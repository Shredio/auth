<?php declare(strict_types = 1);

namespace Shredio\Auth\Context;

use Shredio\Auth\Identity\UserIdentity;

/**
 * @template T of object
 */
interface CurrentUserContext
{

	public function getIdentity(): ?UserIdentity;

	/**
	 * @return T|null
	 */
	public function getEntity(): ?object;

}
