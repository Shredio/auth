<?php declare(strict_types = 1);

namespace Shredio\Auth\User;

interface User
{

	/**
	 * @return non-empty-string
	 */
	public function getUserIdAsString(): string;

	/**
	 * @return list<string>
	 */
	public function getRoles(): array;

}
