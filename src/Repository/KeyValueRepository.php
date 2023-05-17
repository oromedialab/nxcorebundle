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
        $isSingleRecord = !empty($key);
        $dql = '
            SELECT
                key_value
            FROM
                App:KeyValue key_value
            WHERE
                key_value.keyName IN (:keys)
        ';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter(':keys', true === $isSingleRecord ? [$key] : $keys);
        $arrayResult = $query->getArrayResult();
        $items = [];
        foreach ($arrayResult as $row) {
            $items[] = [
                'uuid' => $row['uuid'],
                'key' => $row['keyName'],
                'value' => $row['keyValue'],
                'updated_at' => $row['updatedAt']->format(\DateTime::RFC3339)
            ];
        }
        if (empty($items)) {
            return [];
        }
        return true === $isSingleRecord && !empty($items[0]) ? $items[0] : $items;
    }
}
