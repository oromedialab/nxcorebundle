<?php

namespace OroMediaLab\NxCoreBundle\Controller\V1;

use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use OroMediaLab\NxCoreBundle\Utils\ApiResponse;
use OroMediaLab\NxCoreBundle\Enum\ApiResponseCode;
use OroMediaLab\NxCoreBundle\Entity\KeyValue;
use OroMediaLab\NxCoreBundle\Entity\User;

class KeyValueController extends BaseController
{
    public function save(Request $request, ManagerRegistry $doctrine, #[CurrentUser] ?User $user): ApiResponse
    {
        $entityManager = $doctrine->getManager();
        $postData = $request->request->all();
        $uuid = !empty($postData['uuid']) ? $postData['uuid'] : null;
        $isEdit = 'api_v1_key_value_update' === $request->get('_route');
        $keyValueEntity = null;
        if (true === $isEdit) {
            $keyValueEntity = $doctrine->getRepository(KeyValue::class)->findOneBy(['uuid' => $uuid]);
            if (!$keyValueEntity) {
                return new ApiResponse(ApiResponseCode::NOT_FOUND);
            }
        }
        if (!$keyValueEntity) {
            $keyValueEntity = $doctrine->getRepository(KeyValue::class)->findOneBy(['keyName' => $postData['key']]);
            if ($keyValueEntity) {
                return new ApiResponse(ApiResponseCode::DUPLICATE_RESOURCE);
            }
            $keyValueEntity = new KeyValue();
        }
        $keyValueEntity->setKeyName($postData['key']);
        $keyValueEntity->setKeyValue($postData['value']);
        if ($user) {
            $keyValueEntity->setUser($user);
        }
        $entityManager->persist($keyValueEntity);
        $entityManager->flush();
        return new ApiResponse($isEdit ? ApiResponseCode::RESOURCE_UPDATED : ApiResponseCode::RESOURCE_CREATED);
    }

    public function update(Request $request, ManagerRegistry $doctrine): ApiResponse
    {
        return $this->forward('App\Controller\Api\V1\KeyValueController::save', [
            'uuid' => $request->get('uuid'),
            '_route' => $request->attributes->get('_route'),
            '_route_params' => $request->attributes->get('_route_params')
        ]);
    }

    public function index(Request $request, ManagerRegistry $doctrine): ApiResponse
    {
        $entityManager = $doctrine->getManager();
        $params = $request->query->all();
        $items = $doctrine->getRepository(KeyValue::class)->fetchAll([
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
        $entity = $entityManager->getRepository(KeyValue::class)->findOneBy(['uuid' => $request->get('uuid')]);
        if (!$entity) {
            return new ApiResponse(ApiResponseCode::NOT_FOUND);
        }
        $entityManager->remove($entity);
        $entityManager->flush();
        return new ApiResponse(ApiResponseCode::RESOURCE_DELETED);
    }
}
