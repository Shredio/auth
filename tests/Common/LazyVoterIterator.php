<?php declare(strict_types = 1);

namespace Tests\Common;

use ArrayIterator;
use IteratorAggregate;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Traversable;

/**
 * @implements IteratorAggregate<VoterInterface>
 */
final class LazyVoterIterator implements IteratorAggregate
{

	/**
	 * @param list<VoterInterface> $list
	 * @param list<VoterInterface> $append
	 */
	public function __construct(
		public array $list = [],
		public array $append = [],
	)
	{
	}

	public function getIterator(): Traversable
	{
		return new ArrayIterator(array_merge($this->list, $this->append));
	}

}
