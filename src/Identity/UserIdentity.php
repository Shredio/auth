<?php declare(strict_types = 1);

namespace Shredio\Auth\Identity;

interface UserIdentity
{

	/**
	 * @return non-empty-string|int
	 */
	public function getId(): string|int;

	/**
	 * @return list<string>
	 */
	public function getRoles(): array;

}
