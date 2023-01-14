<?php
namespace OroMediaLab\NxCoreBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestValidationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'validate',
        );
    }

    public function validate(ControllerEvent $event)
    {
        $request = $event->getRequest();
        $routeName = $request->get('_route');
        $data = !empty($request->request->all()) ? $request->request->all() : array();
    }
}
