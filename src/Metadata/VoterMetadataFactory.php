<?php declare(strict_types = 1);

namespace Shredio\Auth\Metadata;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use Shredio\Auth\Attribute\VoteMethod;
use Shredio\Auth\Context\VoterContext;
use Shredio\Auth\Exception\InvalidVoterException;
use Shredio\Auth\Exception\InvalidVoterParameterException;
use Shredio\Auth\Entity\UserEntity;
use Shredio\Auth\Requirement\Requirement;
use Shredio\Auth\Service\VoterService;
use Shredio\Auth\UserRequirementChecker;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @phpstan-import-type ParameterMetadataType from VoterMetadata
 */
final readonly class VoterMetadataFactory
{

	private const array VoterReturnValues = ['bool' => true, 'true' => true, 'false' => true, 'null' => true];

	public function __construct(
		private ?string $nameConventionForMethods = null,
	)
	{
	}

	/**
	 * @param class-string $voter
	 */
	public function create(string $voter): VoterMetadata
	{
		return $this->createBuilder($voter)->build();
	}

	/**
	 * @param class-string $voter
	 */
	private function createBuilder(string $voter): VoterMetadataBuilder
	{
		$reflection = new ReflectionClass($voter);
		$builder = new VoterMetadataBuilder($voter);

		foreach ($reflection->getMethods() as $method) {
			if (str_starts_with($method->name, '__')) {
				continue;
			}

			if ($method->isStatic() || $method->isAbstract()) {
				continue;
			}

			$attributes = $method->getAttributes(VoteMethod::class);

			if (!$attributes) {
				if ($this->nameConventionForMethods && str_starts_with($method->name, $this->nameConventionForMethods)) {
					$this->throwMethodException($method, 'probably missing #[VoteMethod] attribute');
				}

				continue;
			}

			if (count($attributes) > 1) {
				$this->throwMethodException($method, 'must have only one #[VoteMethod] attribute');
			}

			if (!$method->isPublic()) {
				$this->throwMethodException($method, 'must not be public');
			}

			$this->extract($builder, $method);

			$this->checkMethodReturnType($method);
		}

		if ($builder->isEmpty()) {
			$this->throwClassException($reflection, 'missing any vote method with attributes');
		}

		return $builder;
	}

	private function extract(VoterMetadataBuilder $builder, ReflectionMethod $method): void
	{
		$reflectionParameters = $method->getParameters();

		if (!$reflectionParameters) {
			$this->throwMethodException($method, 'must have at least one parameter');
		}

		$requirements = $this->extractRequirements($method, array_shift($reflectionParameters));
		$parameters = $this->extractMethodParameters($method, $reflectionParameters);

		foreach ($requirements as $requirement) {
			$builder->addMetadata($method->name, $requirement, $parameters);
		}
	}

	/**
	 * @param list<ReflectionParameter> $parameters
	 * @return list<ParameterMetadataType>
	 */
	private function extractMethodParameters(ReflectionMethod $method, array $parameters): array
	{
		$values = [];

		foreach ($parameters as $parameter) {
			$types = $this->getClassNameTypes($method, $parameter, $parameter->getType());

			if (count($types->classNames) > 1) {
				$this->throwParameterException($method, $parameter, 'only one type-hint is allowed');
			}

			$className = $types->classNames[0] ?? null;

			if ($className === null) {
				$this->throwParameterException($method, $parameter, 'must have type-hint to resolve');
			}

			$nullable = $types->nullable;
			$serviceClassName = null;

			if (is_a($className, UserEntity::class, true)) {
				$scope = ParameterScope::UserEntity;
			} else if ($className === VoterContext::class) {
				$scope = ParameterScope::Context;

				if ($nullable) {
					$this->throwParameterException($method, $parameter, 'unnecessary nullable type-hint');
				}
			} else if ($className === UserRequirementChecker::class) {
				$scope = ParameterScope::RequirementChecker;

				if ($nullable) {
					$this->throwParameterException($method, $parameter, 'unnecessary nullable type-hint');
				}
			} else if (is_a($className, VoterService::class, true)) {
				$scope = ParameterScope::Custom;
				$serviceClassName = $className;
			} else {
				if (is_a($className, UserInterface::class, true)) {
					$this->throwParameterException(
						$method,
						$parameter,
						sprintf('must implements %s', UserEntity::class),
					);
				}
				$this->throwParameterException($method, $parameter, 'unresolvable type-hint');
			}

			$values[] = [
				'scope' => $scope->value,
				'serviceClassName' => $serviceClassName,
				'nullable' => $nullable,
			];
		}

		return $values;
	}

	private function getClassNameTypes(ReflectionMethod $method, ReflectionParameter $parameter, ?ReflectionType $type): ParameterClassNames
	{
		if (!$type) {
			$this->throwParameterException($method, $parameter, 'must have type-hint to resolve');
		}

		$nullable = $type->allowsNull();

		if ($type instanceof ReflectionUnionType) {
			$obj = new ParameterClassNames([], $nullable);

			foreach ($type->getTypes() as $type) {
				$obj = $obj->merge($this->getClassNameTypes($method, $parameter, $type));
			}

			return $obj;
		}

		if (!$type instanceof ReflectionNamedType) {
			$this->throwParameterException($method, $parameter, 'complex type-hint cannot be resolved');
		}

		if ($type->isBuiltin()) { // probably attribute
			if ($type->getName() === 'null') {
				return new ParameterClassNames([], $nullable);
			}

			$this->throwParameterException($method, $parameter, 'only class type-hint is allowed');
		}

		/** @var class-string $class */
		$class = $type->getName();

		return new ParameterClassNames([$class], $nullable);
	}

	/**
	 * @param ReflectionClass<object> $class
	 */
	private function throwClassException(ReflectionClass $class, string $message): never
	{
		throw new InvalidVoterException(sprintf(
			'Class %s %s',
			$class->name,
			$message,
		));
	}

	private function throwMethodException(ReflectionMethod $method, string $message): never
	{
		throw new InvalidVoterException(sprintf(
			'Method %s::%s() %s',
			$method->getDeclaringClass()->name,
			$method->name,
			$message,
		));
	}

	private function throwParameterException(ReflectionMethod $method, ReflectionParameter $parameter, string $message): never
	{
		throw new InvalidVoterParameterException(sprintf(
			'Parameter $%s of %s::%s() %s',
			$parameter->name,
			$method->getDeclaringClass()->name,
			$method->name,
			$message,
		));
	}

	private function checkMethodReturnType(ReflectionMethod $method): void
	{
		$type = $method->getReturnType();

		if (!$type) {
			$this->throwMethodException($method, 'must have return type-hint');
		}

		if (!$type instanceof ReflectionNamedType || !isset(self::VoterReturnValues[$type->getName()])) {
			$this->throwMethodException($method, 'must returns bool or null');
		}
	}

	/**
	 * @return list<class-string<Requirement>>
	 */
	private function extractRequirements(ReflectionMethod $method, ReflectionParameter $parameter): array
	{
		$types = $this->getClassNameTypes($method, $parameter, $parameter->getType());

		if (!$types->classNames) {
			$this->throwParameterException($method, $parameter, 'must have type-hint to resolve');
		}

		if ($types->nullable) {
			$this->throwParameterException($method, $parameter, 'unnecessary nullable type-hint');
		}

		$classNames = [];

		foreach ($types->classNames as $className) {
			if (!is_a($className, Requirement::class, true)) {
				$this->throwParameterException($method, $parameter, 'first parameter must be instance of Requirement');
			}

			$classNames[] = $className;
		}

		return $classNames;
	}

}
