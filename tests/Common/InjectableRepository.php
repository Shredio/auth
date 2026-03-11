<?php declare(strict_types = 1);

namespace Tests\Common;

use Shredio\Auth\Service\VoterInjectable;

final class InjectableRepository implements VoterInjectable
{

	public function hasAccess(int $userId): bool
	{
		return $userId === 1;
	}

}
