<?php declare(strict_types = 1);

namespace Shredio\Auth\Metadata;

use OutOfBoundsException;
use Shredio\Auth\Requirement\Requirement;

/**
 * @phpstan-type ParameterMetadataType array{ scope: value-of<ParameterScope>, serviceClassName: class-string|null, nullable: bool }
 * @phpstan-type MetadataType array<class-string<Requirement>, array{
 *      method: string,
 *      parameters: list<ParameterMetadataType>,
 *  }>
 */
final readonly class VoterMetadata
{

	/**
	 * @param MetadataType $metadata
	 */
	public function __construct(
		public string $className,
		private array $metadata,
	)
	{
	}

	/**
	 * @param class-string<Requirement> $requirement
	 */
	public function hasRequirement(string $requirement): bool
	{
		return isset($this->metadata[$requirement]);
	}

	public function hasAttribute(string $attribute): bool
	{
		return isset($this->metadata[$attribute]);
	}

	/**
	 * @param class-string<Requirement> $requirementName
	 */
	public function getMethodName(string $requirementName): ?string
	{
		return $this->metadata[$requirementName]['method'] ?? null;
	}

	/**
	 * @param class-string<Requirement> $requirementName
	 * @return list<ParameterMetadataType>
	 */
	public function getParameterSchema(string $requirementName, string $method): array
	{
		$metadata = $this->metadata[$requirementName] ?? throw new OutOfBoundsException(sprintf('Requirement method for %s not found.', $requirementName));

		if ($metadata['method'] !== $method) {
			throw new OutOfBoundsException(sprintf('Mismatched method "%s" for requirement "%s".', $method, $requirementName));
		}

		return $metadata['parameters'];
	}

	/**
	 * @return list<mixed>
	 */
	public function toArguments(): array
	{
		return [
			$this->className,
			$this->metadata,
		];
	}

}
