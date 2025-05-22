<?php declare(strict_types = 1);

namespace Shredio\Auth\Metadata;

final readonly class ParameterClassNames
{

	/**
	 * @param list<class-string> $classNames
	 */
	public function __construct(
		public array $classNames,
		public bool $nullable = false,
	)
	{
	}

	public function merge(ParameterClassNames $obj): self
	{
		return new self(array_merge($this->classNames, $obj->classNames), $this->nullable || $obj->nullable);
	}

}
