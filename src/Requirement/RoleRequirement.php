<?php declare(strict_types = 1);

namespace Shredio\Auth\Requirement;

interface RoleRequirement extends Requirement
{

	/**
	 * @return non-empty-list<string>
	 */
	public function getRoles(): array;

}
