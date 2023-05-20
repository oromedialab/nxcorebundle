<?php

namespace OroMediaLab\NxCoreBundle\Repository;

use OroMediaLab\NxCoreBundle\Entity\KeyValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class KeyValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KeyValue::class);
    }

    public function fetchAll(array $params = array()): ?array
    {
        $keys = !empty($params['filter']['keys']) ? explode(',', str_replace(' ', '', $params['filter']['keys'])) : array();
        $key = !empty($params['filter']['key']) ? $params['filter']['key'] : null;
        $userUuid = [];
        if (!empty($params['filter']['user_uuid']) && is_string($params['filter']['user_uuid'])) {
            $userUuid = [$params['filter']['user_uuid']];
        }
        if (!empty($params['filter']['user_uuid']) && is_array($params['filter']['user_uuid'])) {
            $userUuid = $params['filter']['user_uuid'];
        }
        $isSingleRecord = !empty($key) && 'all' !== $key;
        $dql = '
            SELECT
                key_value,
                user
            FROM
                OroMediaLab\NxCoreBundle\Entity\KeyValue key_value
            LEFT JOIN
                key_value.user user
            WHERE
                1 = 1
        ';
        if ('all' !== $key) {
            $dql .= ' AND key_value.keyName IN (:keys)';
        }
        if (!empty($userUuid)) {
            $dql .= ' AND user.uuid IN (:user_uuid)';
        }
        $query = $this->getEntityManager()->createQuery($dql);
        if ('all' !== $key) {
            $query->setParameter(':keys', true === $isSingleRecord ? [$key] : $keys);
        }
        if (!empty($userUuid)) {
            $query->setParameter(':user_uuid', $userUuid);
        }
        $arrayResult = $query->getResult();
        $items = [];
        foreach ($arrayResult as $row) {
            $rowUser = $row->getUser();
            $itemUser = null;
            if (!empty($rowUser)) {
                $itemUser = array(
                    'uuid' => (string)$rowUser->getUuid(),
                    'name' => $rowUser->getName()
                );
            }
            $items[] = [
                'uuid' => (string)$row->getUuid(),
                'key' => $row->getKeyName(),
                'value' => $row->getKeyValue(),
                'updated_at' => $row->getUpdatedAt()->format(\DateTime::RFC3339),
                'user' => $itemUser
            ];
        }
        if (empty($items)) {
            return [];
        }
        return true === $isSingleRecord && !empty($items[0]) ? $items[0] : $items;
    }
}
