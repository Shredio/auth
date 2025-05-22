<?php declare(strict_types = 1);

namespace Shredio\Auth\Doctrine;

use Doctrine\Persistence\ManagerRegistry;
use LogicException;
use Shredio\Auth\Identity\UserEntityProvider;

/**
 * @template T of object
 * @implements UserEntityProvider<T>
 */
final readonly class DoctrineUserEntityProvider implements UserEntityProvider
{

	/**
	 * @param class-string<T> $className
	 */
	public function __construct(
		private string $className,
		private ManagerRegistry $managerRegistry,
	)
	{
	}

	public function getUserEntity(int|string $id): ?object
	{
		$manager = $this->managerRegistry->getManagerForClass($this->className);

		if ($manager === null) {
			throw new LogicException(sprintf(
				'No entity manager found for class "%s".',
				$this->className,
			));
		}

		/** @var T|null */
		return $manager->find($this->className, $id);
	}

}
