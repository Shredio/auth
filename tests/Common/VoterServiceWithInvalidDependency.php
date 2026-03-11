<?php declare(strict_types = 1);

namespace Tests\Common;

use Shredio\Auth\Context\VoterContext;
use Shredio\Auth\Service\VoterService;
use stdClass;

final readonly class VoterServiceWithInvalidDependency extends VoterService
{

	public function __construct(
		VoterContext $context,
		private stdClass $invalid,
	)
	{
		parent::__construct($context);
	}

}
