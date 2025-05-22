<?php declare(strict_types = 1);

namespace Shredio\Auth\Symfony;

use Shredio\Auth\Requirement\RoleRequirement;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\CacheableVoterInterface;

final readonly class SymfonyRoleVoter implements CacheableVoterInterface
{

	public function __construct(
		private AccessDecisionManagerInterface $accessDecisionManager,
	)
	{
	}

	public function supportsAttribute(string $attribute): bool
	{
		if (!str_contains($attribute, '\\')) {
			return false;
		}

		return is_a($attribute, RoleRequirement::class, true);
	}

	public function supportsType(string $subjectType): bool
	{
		return true;
	}

	/**
	 * @param mixed[] $attributes
	 * @return 1|-1
	 */
	public function vote(TokenInterface $token, mixed $subject, array $attributes): int
	{
		assert($subject instanceof RoleRequirement);

		return $this->accessDecisionManager->decide($token, $subject->getRoles()) ? self::ACCESS_GRANTED : self::ACCESS_DENIED;
	}

}
