<?php declare(strict_types = 1);

namespace Shredio\Auth\Symfony\EventListener;

use Shredio\Auth\Attribute\IsSatisfied;
use Shredio\Auth\Context\CurrentUserContext;
use Shredio\Auth\Exception\ForbiddenException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class IsSatisfiedAttributeListener implements EventSubscriberInterface
{

	public function __construct(
		private CurrentUserContext $currentUserContext,
	)
	{
	}

	public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
	{
		/** @var list<IsSatisfied>|null $attributes */
		$attributes = $event->getAttributes()[IsSatisfied::class] ?? null;
		if ($attributes === null) {
			return;
		}

		try {
			foreach ($attributes as $attribute) {
				$this->currentUserContext->require($attribute->requirement);
			}
		} catch (ForbiddenException $exception) {
			if ($statusCode = $attribute->statusCode) {
				throw new HttpException($statusCode, $exception->getMessage(), $exception);
			}

			$e = new AccessDeniedException($exception->getMessage(), $exception);
			$e->setSubject($attribute->requirement);
			if ($exception->decision !== null) {
				$e->setAccessDecision($exception->decision);
			}

			throw $e;
		}
	}

	public static function getSubscribedEvents(): array
	{
		return [KernelEvents::CONTROLLER_ARGUMENTS => ['onKernelControllerArguments', 20]];
	}

}
