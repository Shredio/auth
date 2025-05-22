<?php declare(strict_types = 1);

namespace Shredio\Auth\Symfony\Bundle;

use LogicException;
use Shredio\Auth\Metadata\VoterMetadata;
use Shredio\Auth\Metadata\VoterMetadataFactory;
use Shredio\Auth\Resolver\VoterParameterResolver;
use Shredio\Auth\Symfony\Adapter\VoterAdapter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final readonly class AuthCompilerPass implements CompilerPassInterface
{

	public function process(ContainerBuilder $container): void
	{
		$config = $container->getExtensionConfig('voter')[0] ?? [];
		$refresh = $container->getParameterBag()->resolveValue($config['refresh'] ?? false);

		$nameConventionForMethods = $config['name_convention_for_methods'] ?? null;

		if ($nameConventionForMethods !== null && !is_string($nameConventionForMethods)) {
			throw new LogicException('The name_convention_for_methods parameter must be a string or null.');
		}

		$metadataFactory = new VoterMetadataFactory($nameConventionForMethods);

		foreach ($container->findTaggedServiceIds('auth.voter') as $serviceId => $tags) {
			$definition = $container->getDefinition($serviceId);
			$className = $definition->getClass();

			if ($className === null) {
				throw new LogicException(sprintf('Cannot get class of %s voter to enhance.', $serviceId));
			}

			if (!class_exists($className)) {
				throw new LogicException(sprintf('Voter class %s does not exist.', $className));
			}

			$adapter = new Definition(VoterAdapter::class, [
				new Reference($serviceId),
				new Reference(VoterParameterResolver::class),
			]);

			if (!$refresh) {
				$metadata = new Definition(VoterMetadata::class, $metadataFactory->create($className)->toArguments());
				$adapter->addMethodCall('setMetadata', [$metadata]);
			}

			$adapter->addTag('security.voter');

			$container->setDefinition(sprintf('%s.adapter', $serviceId), $adapter);
		}
	}

}
