<?php

namespace OroMediaLab\NxCoreBundle\Controller\V1;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use OroMediaLab\NxCoreBundle\Utils\ApiResponse;
use OroMediaLab\NxCoreBundle\Enum\ApiResponseCode;
use OroMediaLab\NxCoreBundle\Entity\User;
use OroMediaLab\NxCoreBundle\Entity\KeyValue;

class UserController extends BaseController
{
    public function save(
        Request $request,
        ManagerRegistry $doctrine,
        UserPasswordHasherInterface $passwordHasher,
        EventDispatcherInterface $dispatcher
    ): ApiResponse {
        $entityManager = $doctrine->getManager();
        $postData = $request->request->all();
        $uuid = !empty($postData['uuid']) ? $postData['uuid'] : null;
        $isEdit = 'nxcore.routes.api_v1_user_update' === $request->get('_route');
        $events = array(
            'OnCreated' => false === $isEdit,
            'OnUpdated' => true === $isEdit
        );
        $user = $request->get('user');
        if (!empty($uuid) && true === $isEdit) {
            $user = $doctrine->getRepository(User::class)->findOneBy(['uuid' => $uuid]);
            if (!$user) {
                return new ApiResponse(ApiResponseCode::NOT_FOUND);
            }
        }
        if (!$user) {
            $user = new User;
            $user->setRole($postData['role']);
        }
        $user->setName($postData['name']);
        $user->setEmailAddress($postData['email_address']);
        $user->setUsername($postData['email_address']);
        $user->setContactNumber($postData['contact_number']);
        if (!empty($postData['password'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $postData['password']));
        }
        $user->setEnabled($postData['enabled']);
        $entityManager->persist($user);
        $entityManager->flush();
        foreach ($events as $eventName => $eventValue) {
            if (true === $eventValue) {
                $eventFCQN = '\OroMediaLab\NxCoreBundle\Event\Entity\User\\'.$eventName;
                $dispatcher->dispatch(new $eventFCQN($user), $eventFCQN::NAME);
            }
        }
        return new ApiResponse(true === $isEdit ? ApiResponseCode::RESOURCE_UPDATED : ApiResponseCode::RESOURCE_CREATED);
    }

    public function update(Request $request, ManagerRegistry $doctrine): Response
    {
        return $this->forward('nxcore.controller.v1.user_controller::save', [
            'uuid' => $request->get('uuid'),
            '_route' => $request->attributes->get('_route'),
            '_route_params' => $request->attributes->get('_route_params')
        ]);
    }

    public function index(Request $request, ManagerRegistry $doctrine): ApiResponse
    {
        $entityManager = $doctrine->getManager();
        $params = $request->query->all();
        $items = $doctrine->getRepository(User::class)->fetchAll([
            'filter' => $params
        ]);
        if (!empty($params['uuid']) && empty($items)) {
            return new ApiResponse(ApiResponseCode::NOT_FOUND);
        }
        return new ApiResponse(ApiResponseCode::FETCH_SUCCESS, $items);
    }

    public function delete(Request $request, ManagerRegistry $doctrine): ApiResponse
    {
        $entityManager = $doctrine->getManager();
        $entity = $entityManager->getRepository(User::class)->findOneBy(['uuid' => $request->get('uuid')]);
        if (!$entity) {
            return new ApiResponse(ApiResponseCode::NOT_FOUND);
        }
        // Delete all associated KeyValue
        $entityManager->getRepository(KeyValue::class)->deleteAllForUser($entity);
        // Delete User Entity
        $entityManager->remove($entity);
        $entityManager->flush();
        return new ApiResponse(ApiResponseCode::RESOURCE_DELETED);
    }
}
