<?php declare(strict_types = 1);

namespace Tests\Common;

use Shredio\Auth\Context\VoterContext;
use Shredio\Auth\Service\VoterService;

final readonly class VoterServiceWithDependency extends VoterService
{

	public function __construct(
		VoterContext $context,
		private InjectableRepository $repository,
	)
	{
		parent::__construct($context);
	}

	public function hasAccess(int $userId): bool
	{
		return $this->repository->hasAccess($userId);
	}

}
