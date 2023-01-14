<?php
namespace OroMediaLab\NxCoreBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestDataSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'convertJsonStringToArray',
        );
    }

    public function convertJsonStringToArray(ControllerEvent $event)
    {
        $request = $event->getRequest();
        if ($request->getContentType() !== 'json' || empty($request->getContent())) {
            return;
        }
        $data = $this->initData($request);
        $request->request->replace(is_array($data) ? $data : array());
    }

    public function initData($request)
    {
        $jsonBody = !empty($request->getContent()) ? json_decode($request->getContent(), true) : array();
        $postData = !empty($request->request->all()) ? $request->request->all() : array();
        $data = !empty($jsonBody) && is_array($jsonBody) ? $jsonBody : array();
        if (!empty($postData) && is_array($postData)) {
            $data = array_merge($data, $postData);
        }
        return $data;
    }
}
