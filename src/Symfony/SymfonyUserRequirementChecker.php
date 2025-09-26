<?php declare(strict_types = 1);

namespace Shredio\Auth\Symfony;

use Shredio\Auth\Entity\UserEntity;
use Shredio\Auth\Requirement\Requirement;
use Shredio\Auth\Symfony\Token\SymfonyStaticToken;
use Shredio\Auth\UserRequirementChecker;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

final class SymfonyUserRequirementChecker implements UserRequirementChecker
{

	private bool $isSuperAdmin = false;

	public function __construct(
		private readonly AccessDecisionManagerInterface $accessDecisionManager,
	)
	{
	}

	public function isSatisfied(?UserEntity $entity, Requirement $requirement): bool
	{
		if ($this->isSuperAdmin) {
			return true;
		}

		$token = $entity === null ? new NullToken() : new SymfonyStaticToken($entity);
		return $this->decide($token, $requirement);
	}

	/**
	 * Dangerously skips all permission checks. Use with caution!
	 */
	public function dangerouslySkipPermissions(): void
	{
		$this->isSuperAdmin = true;
	}

	private function decide(TokenInterface $token, Requirement $requirement): bool
	{
		return $this->accessDecisionManager->decide($token, [$requirement::class], $requirement);
	}

}
