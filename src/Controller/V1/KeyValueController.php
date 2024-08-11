<?php

namespace OroMediaLab\NxCoreBundle\Controller\V1;

use Symfony\Component\Security\Http\Attribute\CurrentUser;
use OroMediaLab\NxCoreBundle\Attribute\ValidateRequest;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use OroMediaLab\NxCoreBundle\Utils\ApiResponse;
use OroMediaLab\NxCoreBundle\Enum\ApiResponseCode;
use OroMediaLab\NxCoreBundle\Entity\KeyValue;
use OroMediaLab\NxCoreBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;

class KeyValueController extends BaseController
{
    #[ValidateRequest(rules: [
        'key' => [new Assert\NotBlank(), new Assert\Length(['min' => 1, 'max' => 40])],
        'value' => [new Assert\NotBlank(), new Assert\Length(['min' => 1, 'max' => 50000])]
    ])]
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

    #[ValidateRequest(rules: [
        'uuid' => [new Assert\Uuid()],
        'key' => [new Assert\NotBlank(), new Assert\Length(['min' => 1, 'max' => 40])],
        'value' => [new Assert\NotBlank(), new Assert\Length(['min' => 1, 'max' => 50000])]
    ])]
    public function update(Request $request, ManagerRegistry $doctrine,  #[CurrentUser] ?User $user): ApiResponse
    {
        return $this->save($request, $doctrine, $user);
    }

    #[ValidateRequest(rules: ['key' => [new Assert\Type('string'), new Assert\NotBlank()]])]
    public function index(Request $request, ManagerRegistry $doctrine): ApiResponse
    {
        $params = $request->query->all();
        $items = $doctrine->getRepository(KeyValue::class)->fetchAll([
            'filter' => $params,
            'params' => [
                'skip_user_records' => true
            ]
        ]);
        if (empty($items)) {
            return new ApiResponse(ApiResponseCode::NOT_FOUND);
        }
        return new ApiResponse(ApiResponseCode::FETCH_SUCCESS, $items);
    }

    #[ValidateRequest(rules: ['uuid' => [new Assert\Uuid()]])]
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
