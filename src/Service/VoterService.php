<?php declare(strict_types = 1);

namespace Shredio\Auth\Service;

use Shredio\Auth\Context\VoterContext;

abstract readonly class VoterService
{

	final public function __construct(
		protected VoterContext $context,
	)
	{
	}

}
