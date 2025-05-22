<?php declare(strict_types = 1);

namespace Shredio\Auth\Symfony\Adapter;

use LogicException;
use Shredio\Auth\Exception\UnsignedUserException;
use Shredio\Auth\Metadata\VoterMetadata;
use Shredio\Auth\Metadata\VoterMetadataFactory;
use Shredio\Auth\Requirement\Requirement;
use Shredio\Auth\Resolver\VoterParameterResolver;
use Shredio\Auth\Symfony\Identity\SymfonyUserIdentity;
use Shredio\Auth\User\User;
use Shredio\Auth\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\CacheableVoterInterface;

final class VoterAdapter implements CacheableVoterInterface
{

	private readonly VoterMetadataFactory $metadataFactory;

	private ?VoterMetadata $metadata = null;

	public function __construct(
		private readonly Voter $voter,
		private readonly VoterParameterResolver $parameterResolver,
	)
	{
		$this->metadataFactory = new VoterMetadataFactory();
	}

	public function supportsAttribute(string $attribute): bool
	{
		return $this->getMetadata()->hasAttribute($attribute);
	}

	public function supportsType(string $subjectType): bool
	{
		return true;
	}

	/**
	 * @param string[] $attributes
	 */
	public function vote(TokenInterface $token, mixed $subject, array $attributes): int
	{
		if (!$subject instanceof Requirement) {
			return self::ACCESS_ABSTAIN;
		}

		$metadata = $this->getMetadata();
		$method = $metadata->getMethodName($subject::class);

		if (!$method) {
			return self::ACCESS_ABSTAIN;
		}

		$user = $token->getUser();

		if ($user && !$user instanceof User) {
			throw new LogicException(sprintf('User must be an instance of %s, got %s.', User::class, $user::class));
		}

		$identity = $user !== null ? new SymfonyUserIdentity($token) : null;

		try {
			$args = $this->parameterResolver->resolve(
				$identity,
				$user,
				$subject,
				$metadata->getParameterSchema($subject::class, $method),
			);
		} catch (UnsignedUserException) {
			return self::ACCESS_DENIED;
		}

		// @phpstan-ignore-next-line
		if (call_user_func_array([$this->voter, $method], $args)) {
			return self::ACCESS_GRANTED;
		}

		return self::ACCESS_DENIED;
	}

	/**
	 * @internal
	 */
	final public function setMetadata(?VoterMetadata $metadata): void
	{
		$this->metadata = $metadata;
	}

	private function getMetadata(): VoterMetadata
	{
		return $this->metadata ??= $this->metadataFactory->create($this->voter::class);
	}

}
