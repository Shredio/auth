<?php declare(strict_types = 1);

namespace Shredio\Auth\Symfony;

use LogicException;
use Shredio\Auth\Identity\EntityUserIdentity;
use Shredio\Auth\Identity\UserIdentity;
use Shredio\Auth\Identity\UserIdentityFactory;
use Shredio\Auth\Requirement\Requirement;
use Shredio\Auth\Symfony\Identity\SymfonyUserIdentity;
use Shredio\Auth\UserRequirementChecker;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

final readonly class SymfonyUserRequirementChecker implements UserRequirementChecker
{

	public function __construct(
		private AccessDecisionManagerInterface $accessDecisionManager,
		private UserIdentityFactory $userIdentityFactory,
	)
	{
	}

	public function isSatisfied(?UserIdentity $identity, Requirement $requirement): bool
	{
		if ($identity instanceof EntityUserIdentity) {
			$identity = $this->userIdentityFactory->create($identity->getEntity());

			if (!$identity instanceof SymfonyUserIdentity) {
				throw new LogicException(sprintf(
					'Identity factory %s did not return a SymfonyUserIdentity, but "%s".',
					$this->userIdentityFactory::class,
					$identity::class,
				));
			}
		}

		if ($identity instanceof SymfonyUserIdentity) {
			return $this->decide($identity->token, $requirement);
		} else if ($identity === null) {
			return $this->decide(new NullToken(), $requirement);
		}

		throw new LogicException(sprintf(
			'Unsupported identity type "%s".',
			$identity::class,
		));
	}

	private function decide(TokenInterface $token, Requirement $requirement): bool
	{
		return $this->accessDecisionManager->decide($token, [$requirement::class], $requirement);
	}

}
