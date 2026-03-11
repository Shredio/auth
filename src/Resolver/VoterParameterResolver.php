<?php declare(strict_types = 1);

namespace Shredio\Auth\Resolver;

use LogicException;
use Psr\Container\ContainerInterface;
use Shredio\Auth\Context\VoterContext;
use Shredio\Auth\Exception\UnsignedUserException;
use Shredio\Auth\Entity\UserEntity;
use Shredio\Auth\Metadata\ParameterScope;
use Shredio\Auth\Metadata\VoterMetadata;
use Shredio\Auth\Requirement\Requirement;
use Shredio\Auth\UserRequirementChecker;

/**
 * @phpstan-import-type ParameterMetadataType from VoterMetadata
 */
final readonly class VoterParameterResolver
{

	public function __construct(
		private UserRequirementChecker $userRequirementChecker,
		private ?ContainerInterface $serviceLocator = null,
	)
	{
	}

	/**
	 * @param ParameterMetadataType[] $parameters
	 * @return list<mixed>
	 *
	 * @throws UnsignedUserException
	 */
	public function resolve(?UserEntity $entity, Requirement $requirement, array $parameters): array
	{
		$createContext = fn () => new VoterContext($requirement, $entity, $this->userRequirementChecker);
		$args = [$requirement];

		foreach ($parameters as $parameter) {
			$scope = ParameterScope::from($parameter['scope']);

			$args[] = match ($scope) {
				ParameterScope::RequirementChecker => $this->userRequirementChecker,
				ParameterScope::UserEntity => $this->resolveNullable($entity, $parameter['nullable']),
				ParameterScope::Context => $context ??= $createContext(),
				ParameterScope::Custom => $this->createService($parameter['serviceClassName'], $context ??= $createContext(), $parameter['dependencies']),
			};
		}

		return $args;
	}

	/**
	 * @template T of object
	 * @param T|null $object
	 * @return T|null
	 * @throws UnsignedUserException
	 */
	private function resolveNullable(?object $object, bool $nullable): ?object
	{
		if ($object) {
			return $object;
		}

		if ($nullable) {
			return null;
		}

		throw new UnsignedUserException('User is not logged in, but required for voter.');
	}

	/**
	 * @param list<class-string> $dependencies
	 */
	private function createService(?string $class, VoterContext $context, array $dependencies): object
	{
		if ($class === null) {
			throw new LogicException('Unexpected error, serviceClassName is required for custom parameter scope.');
		}

		if ($dependencies !== []) {
			if ($this->serviceLocator === null) {
				throw new LogicException(sprintf('Service locator is required to resolve dependencies for %s.', $class));
			}

			$resolved = [];

			foreach ($dependencies as $dependency) {
				$resolved[] = $this->serviceLocator->get($dependency);
			}

			return new $class($context, ...$resolved);
		}

		return new $class($context);
	}

}
