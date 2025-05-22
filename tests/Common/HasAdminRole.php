<?php declare(strict_types = 1);

namespace Tests\Common;

use Shredio\Auth\Requirement\RoleRequirement;

final class HasAdminRole implements RoleRequirement
{

	public function getRoles(): array
	{
		return ['ROLE_ADMIN'];
	}

}
