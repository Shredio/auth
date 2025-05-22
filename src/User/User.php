<?php declare(strict_types = 1);

namespace Shredio\Auth\User;

interface User
{

	/**
	 * @return non-empty-string|int
	 */
	public function getUserId(): string|int;

	/**
	 * @return list<string>
	 */
	public function getRoles(): array;

}
