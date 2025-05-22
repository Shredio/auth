<?php declare(strict_types = 1);

namespace Tests\Common;

use Shredio\Auth\Service\VoterService;

final readonly class MyVoterService extends VoterService
{

	public function isLoggedIn(): bool
	{
		return $this->context->isCurrentUserLoggedIn();
	}

}
