<?php declare(strict_types = 1);

namespace Shredio\Auth\Symfony\Middleware;

use Shredio\Auth\Exception\ForbiddenException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ForbiddenErrorMiddleware implements EventSubscriberInterface
{

	public static function getSubscribedEvents(): array
	{
		return [
			KernelEvents::EXCEPTION => ['onKernelException', 100],
		];
	}

	public function onKernelException(ExceptionEvent $event): void
	{
		if ($event->getThrowable() instanceof ForbiddenException) {
			$event->setResponse(new Response(status: 403));
		}
	}

}
