<?php declare(strict_types = 1);

namespace Shredio\Auth\Metadata;

use LogicException;
use Shredio\Auth\Requirement\Requirement;

/**
 * @phpstan-import-type MetadataType from VoterMetadata
 * @phpstan-import-type ParameterMetadataType from VoterMetadata
 */
final class VoterMetadataBuilder
{

	/** @var MetadataType */
	private array $metadata = [];

	/**
	 * @param class-string $voterClass
	 */
	public function __construct(
		private readonly string $voterClass,
	)
	{
	}

	/**
	 * @param class-string<Requirement> $requirement
	 * @param list<ParameterMetadataType> $parameters
	 */
	public function addMetadata(string $method, string $requirement, array $parameters): void
	{
		if (isset($this->metadata[$requirement])) {
			throw new LogicException(sprintf('Duplicated %s requirement "%s" in "%s".', $requirement, $method, $this->metadata[$requirement]['method']));
		}

		$this->metadata[$requirement] = [
			'method' => $method,
			'parameters' => $parameters,
		];
	}

	public function build(): VoterMetadata
	{
		return new VoterMetadata(
			$this->voterClass,
			$this->metadata,
		);
	}

	public function isEmpty(): bool
	{
		return !$this->metadata;
	}

}
