<?php

declare(strict_types=1);

namespace RestfulBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestSubscriber implements EventSubscriberInterface
{
    private const CONTENT_TYPE_JSON = 'json';
    private const CONTENT_TYPE_APPLICATION_JSON = 'application/json';

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 254],
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!in_array($request->getContentType(), $this->supportsTypes())) {
            return;
        }
        $content = $request->headers->has('data') ? $request->headers->get('data') : $request->getContent();
        $data = json_decode($content, true);
        if (false === $data) {
            throw new BadRequestHttpException('Request data is invalid');
        }

        $request->request->replace(is_array($data) ? $data : []);
    }

    private function supportsTypes(): array
    {
        return [
            self::CONTENT_TYPE_JSON,
            self::CONTENT_TYPE_APPLICATION_JSON,
        ];
    }
}
