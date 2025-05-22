<?php declare(strict_types = 1);

namespace Shredio\Auth\Exception;

use RuntimeException;
use Shredio\Auth\Requirement\Requirement;
use Throwable;

final class ForbiddenException extends RuntimeException
{

	public function __construct(
		int|string|null $userId,
		Requirement $requirement,
		?Throwable $previous = null,
	)
	{
		parent::__construct(sprintf(
			'User %s is not allowed to perform %s.',
			$userId === null ? 'Unauthenticated user' : sprintf('User %s', $userId),
			$requirement::class,
		), previous: $previous);
	}

}
