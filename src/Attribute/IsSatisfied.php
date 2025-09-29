<?php declare(strict_types = 1);

namespace Shredio\Auth\Attribute;

use Attribute;
use Shredio\Auth\Requirement\Requirement;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class IsSatisfied
{

	public function __construct(
		public Requirement $requirement,
		public ?int $statusCode = null,
	)
	{
	}

}
