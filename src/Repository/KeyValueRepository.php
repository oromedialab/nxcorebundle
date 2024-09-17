<?php

namespace OroMediaLab\NxCoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use OroMediaLab\NxCoreBundle\Entity\KeyValue;
use OroMediaLab\NxCoreBundle\Entity\User;

class KeyValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KeyValue::class);
    }

    public function fetchAll(array $params = array()): ?array
    {
        // $params['filter'] is to get directly from url query param
        // $params['params'] is to fetch data internally
        $key = !empty($params['filter']['key']) ? $params['filter']['key'] : null;
        $keys = !empty($params['params']['keys']) ? explode(',', str_replace(' ', '', $params['params']['keys'])) : array();
        $userUuid = [];
        $skipUserRecord = isset($params['params']['skip_user_records']) ? (bool)$params['params']['skip_user_records'] : false;
        if (!empty($params['params']['user_uuid']) && is_string($params['params']['user_uuid'])) {
            $userUuid = [$params['params']['user_uuid']];
        }
        if (!empty($params['params']['user_uuid']) && is_array($params['params']['user_uuid'])) {
            $userUuid = $params['params']['user_uuid'];
        }
        $fetchAll = in_array('all', $keys);
        $dql = 'SELECT key_value';
        if (!empty($userUuid) && false === $skipUserRecord) {
            $dql .= ', user';
        }
        $dql .= ' FROM OroMediaLab\NxCoreBundle\Entity\KeyValue key_value';
        if (!empty($userUuid) && false === $skipUserRecord) {
            $dql .= ' LEFT JOIN key_value.user user';
        }
        $dql .= ' WHERE 1 = 1';
        if (!$fetchAll) {
            $dql .= ' AND key_value.keyName IN (:keys)';
        }
        if (!empty($userUuid) && false === $skipUserRecord) {
            $dql .= ' AND user.uuid IN (:user_uuid)';
        }
        // if (true === $skipUserRecord) {
        //     $dql .= ' AND key_value.user IS NULL';
        // }
        $query = $this->getEntityManager()->createQuery($dql);
        if (!$fetchAll) {
            $query->setParameter(':keys', !empty($key) ? [$key] : $keys);
        }
        if (!empty($userUuid) && false === $skipUserRecord) {
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
        return !empty($key) ? $items[0] : $items;
    }

    public function deleteAllForUser(User $user)
    {
        $query = $this->getEntityManager()->createQuery('
            DELETE FROM
                OroMediaLab\NxCoreBundle\Entity\KeyValue kv
            WHERE
                kv.user = :user'
        );
        $query->setParameter('user', $user);
        $query->execute();
    }
}
