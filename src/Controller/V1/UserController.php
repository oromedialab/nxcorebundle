<?php

namespace OroMediaLab\NxCoreBundle\Controller\V1;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use OroMediaLab\NxCoreBundle\Utils\ApiResponse;
use OroMediaLab\NxCoreBundle\Enum\ApiResponseCode;
use OroMediaLab\NxCoreBundle\Entity\User;
use App\Entity\UserTechnician;
use App\Entity\UserCustomer;

class UserController extends AbstractController
{
    public function save(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher): ApiResponse
    {
        $roleMapper = [
            'ROLE_TECHNICIAN' => UserTechnician::class,
            'ROLE_CUSTOMER' => UserCustomer::class
        ];
        $entityManager = $doctrine->getManager();
        $postData = $request->request->all();
        $uuid = !empty($postData['uuid']) ? $postData['uuid'] : null;
        $isEdit = 'nxcore.routes.api_v1_user_update' === $request->get('_route');
        $userFqcn = $roleMapper[$postData['role']];
        $user = null;
        if (!empty($uuid)) {
            $user = $doctrine->getRepository(User::class)->findOneBy(['uuid' => $uuid]);
            if (!$user) {
                return new ApiResponse(ApiResponseCode::NOT_FOUND);
            }
        }
        if (!$user) {
            $user = new $userFqcn();
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
        $entityManager->remove($entity);
        $entityManager->flush();
        return new ApiResponse(ApiResponseCode::RESOURCE_DELETED);
    }
}
