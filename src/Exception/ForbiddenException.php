<?php declare(strict_types = 1);

namespace Shredio\Auth\Exception;

use Shredio\Auth\Entity\UserEntity;
use Shredio\Auth\Requirement\Requirement;
use Symfony\Component\Security\Core\Authorization\AccessDecision;
use Throwable;

final class ForbiddenException extends RuntimeException
{

	public function __construct(
		public readonly ?UserEntity $userEntity,
		public readonly Requirement $requirement,
		public readonly ?AccessDecision $decision = null,
		?Throwable $previous = null,
	)
	{
		if ($decision !== null) {
			$message = $decision->getMessage();
		} else {
			$message = sprintf(
				'User %s is not allowed to perform %s.',
				$userEntity === null ? 'Unauthenticated user' : sprintf('User %s', $userEntity->getUserIdentifier()),
				$requirement::class,
			);
		}

		parent::__construct($message, previous: $previous);
	}

}
