<?php declare(strict_types = 1);

namespace Shredio\Auth\Symfony;

use Shredio\Auth\Requirement\RoleRequirement;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecision;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\CacheableVoterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

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
	public function vote(TokenInterface $token, mixed $subject, array $attributes, ?Vote $vote = null): int
	{
		assert($subject instanceof RoleRequirement);

		$decision = new AccessDecision();
		$result = $this->accessDecisionManager->decide($token, $subject->getRoles(), $decision) ? self::ACCESS_GRANTED : self::ACCESS_DENIED;
		if ($vote !== null) {
			foreach ($decision->votes as $item) {
				$vote->reasons = array_merge($vote->reasons, $item->reasons);
			}
		}

		return $result;
	}

}
