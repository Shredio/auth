<?php declare(strict_types = 1);

namespace Shredio\Auth\Identity;

interface UserIdentityFactory
{

	public function create(object $user): UserIdentity;

}
