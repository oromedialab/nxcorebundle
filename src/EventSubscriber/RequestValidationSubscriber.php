<?php
namespace OroMediaLab\NxCoreBundle\EventSubscriber;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use OroMediaLab\NxCoreBundle\Attribute\ValidateRequest;
use Symfony\Component\Validator\Constraints as Assert;
use OroMediaLab\NxCoreBundle\Utils\ApiResponse;
use OroMediaLab\NxCoreBundle\Enum\ApiResponseCode;

class RequestValidationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ValidatorInterface $validator
    ) {}

    public static function getSubscribedEvents(): array
    {
        return array(
            KernelEvents::CONTROLLER => 'validate'
        );
    }

    public function validate(ControllerEvent $event)
    {
        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }
        $reflectionMethod = new \ReflectionMethod($controller[0], $controller[1]);
        $attributes = $reflectionMethod->getAttributes(ValidateRequest::class);
        if (empty($attributes)) {
            return;
        }
        $request = $event->getRequest();
        $data = !empty($request->request->all()) ? $request->request->all() : array();
        $data = !empty($request->query->all()) ? array_merge_recursive($request->query->all(), $data) : $data;
        $validateRequest = $attributes[0]->newInstance();
        $rules = $validateRequest->getRules();
        $constraints = new Assert\Collection($rules);
        $violations = $this->validator->validate($data, $constraints);
        $errors = [];
        foreach ($violations as $violation) {
            $key = str_replace(['[', ']'], '', $violation->getPropertyPath());
            $errors[] = [
                'field' => $key,
                'message' => $violation->getMessage()
            ];
        }
        if (count($errors) > 0) {
            $event->setController(function() use ($errors) {
                return new ApiResponse(ApiResponseCode::VALIDATION_FAILED, $errors);
            });
        }
    }
}
