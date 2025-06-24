<?php declare(strict_types = 1);

namespace Shredio\Auth\Symfony\Bundle;

use Shredio\Auth\Context\CurrentUserContext;
use Shredio\Auth\Context\MockCurrentUserContext;
use Shredio\Auth\Identity\UserIdentityFactory;
use Shredio\Auth\Metadata\VoterMetadataFactory;
use Shredio\Auth\Resolver\VoterParameterResolver;
use Shredio\Auth\Symfony\Context\SymfonyCurrentUserContext;
use Shredio\Auth\Symfony\Identity\SymfonyUserIdentityFactory;
use Shredio\Auth\Symfony\SymfonyRoleVoter;
use Shredio\Auth\Symfony\SymfonyUserRequirementChecker;
use Shredio\Auth\UserRequirementChecker;
use Shredio\Auth\Voter;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class AuthBundle extends AbstractBundle
{

	/**
	 * @param mixed[] $config
	 */
	public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();

		$services->set(VoterMetadataFactory::class)
			->args([$config['name_convention_for_methods'] ?? null]);

		$builder->registerForAutoconfiguration(Voter::class)
			->addTag('auth.voter');

		$services->set($this->prefix('requirement_checker'), SymfonyUserRequirementChecker::class)
			->autowire()
			->alias(UserRequirementChecker::class, $this->prefix('requirement_checker'));

		$services->set($this->prefix('current_user_context'), SymfonyCurrentUserContext::class)
			->autowire()
			->alias(CurrentUserContext::class, $this->prefix('current_user_context'));

		$services->set($this->prefix('user_identity_factory'), SymfonyUserIdentityFactory::class)
			->autowire()
			->alias(UserIdentityFactory::class, $this->prefix('user_identity_factory'));

		$services->set($this->prefix('role_voter'), SymfonyRoleVoter::class)
			->autowire();

		$services->set($this->prefix('parameter_resolver'), VoterParameterResolver::class)
			->autowire();

		if ($container->env() === 'test') {
			$services->set(MockCurrentUserContext::ServiceId, MockCurrentUserContext::class)
				->decorate($this->prefix('current_user_context'))
				->args([
					service('.inner'),
					service($this->prefix('requirement_checker')),
				])
				->public();
		}
	}

	public function build(ContainerBuilder $container): void
	{
		$container->addCompilerPass(new AuthCompilerPass(), priority: 90);
	}

	public function configure(DefinitionConfigurator $definition): void
	{
		$definition->rootNode() // @phpstan-ignore-line
			->children()
				->booleanNode('refresh')->defaultTrue()->end()
				->stringNode('name_convention_for_methods')->defaultNull()->end()
			->end();
	}

	private function prefix(string $name): string
	{
		return sprintf('auth.%s', $name);
	}

}
