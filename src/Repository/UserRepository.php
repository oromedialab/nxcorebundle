<?php

namespace OroMediaLab\NxCoreBundle\Repository;

use OroMediaLab\NxCoreBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use OroMediaLab\NxCoreBundle\Entity\KeyValue;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function fetchAll(array $params = array()): ?array
    {
        $q = !empty($params['filter']['q']) ? $params['filter']['q'] : null;
        $page = !empty($params['filter']['page']) ? $params['filter']['page'] : 1;
        $limit = !empty($params['filter']['limit']) ? $params['filter']['limit'] : 30;
        $role = !empty($params['filter']['role']) ? explode(',', $params['filter']['role']): null;
        $uuid = !empty($params['filter']['uuid']) ? $params['filter']['uuid'] : null;
        $enabled = (!empty($params['filter']['enabled']) && 'null' !== $params['filter']['enabled']) ? filter_var($params['filter']['enabled'], FILTER_VALIDATE_BOOLEAN) : null;
        $keyValueNames = !empty($params['filter']['key_value']) ? explode(',', str_replace(' ', '', $params['filter']['key_value'])) : array();
        $firstResult = ($page - 1) * $limit;
        $maxResults = $limit;
        $dql = '
            SELECT
                user
            FROM
                OroMediaLab\NxCoreBundle\Entity\User user
            WHERE
                1 = 1
        ';
        if (null !== $enabled) {
            $dql .= ' AND user.enabled = :enabled';
        }
        $searchForId = strpos($q, '#') === 0;
        if (!empty($q)) {
            if ($searchForId) {
                $dql .= ' AND (user.id = :keyword)';
            } else {
                $dql .= ' AND (user.name LIKE :keyword)';
            }
        }
        if (!empty($role)) {
            $dql .= ' AND user.role = :role';
        }
        if (null !== $uuid) {
            $dql .= ' AND user.uuid = :uuid';
        }
        $dql .= ' ORDER BY user.id DESC';
        $query = $this->getEntityManager()->createQuery($dql);
        if (!empty($q)) {
            if ($searchForId) {
                $q = preg_replace('/[^0-9]/', '', $q);
                $query->setParameter(':keyword', $q);
            } else {
                $query->setParameter(':keyword', '%'.$q.'%');
            }
        }
        if (null !== $enabled) {
            $query->setParameter(':enabled', $enabled);
        }
        if (!empty($role)) {
            $query->setParameter(':role', $role);
        }
        if (null !== $uuid) {
            $query->setParameter(':uuid', $uuid);
        }
        $query->setFirstResult($firstResult);
        $query->setMaxResults($maxResults);
        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $maxResults);
        $items = [];
        $userUuids = array();
        foreach ($paginator as $row) {
            $userUuids[] = (string)$row->getUuid();
            $item = [];
            $item['id'] = $row->getId();
            $item['uuid'] = (string)$row->getUuid();
            $item['name'] = $row->getName();
            $item['email_address'] = $row->getEmailAddress();
            $item['contact_number'] = $row->getContactNumber();
            $item['enabled'] = $row->isEnabled();
            $item['role'] = $row->getRoles()[0];
            $item['created'] = $row->getCreatedAt()->format(\DateTime::RFC3339);
            $items[] = $item;
        }
        if (!empty($keyValueNames)) {
            $keyValuesFilter = array();
            $keyValuesFilter['user_uuid'] = $userUuids;
            if (in_array('all', $keyValueNames)) {
                $keyValuesFilter['key'] = 'all';
            } else {
                $keyValuesFilter['keys'] = implode(',', $keyValueNames);
            }
            $keyValues = $this->getEntityManager()->getRepository(KeyValue::class)->fetchAll([
                'filter' => $keyValuesFilter
            ]);
            $items = array_map(function($value) use ($keyValues, $keyValueNames) {
                foreach ($keyValues as $keyValue) {
                    if (!empty($keyValue['user']['uuid']) && $keyValue['user']['uuid'] === $value['uuid']) {
                        if (in_array('all', $keyValueNames)) {
                            $value[$keyValue['key']] = $keyValue['value'];
                        } else if (in_array($keyValue['key'], $keyValueNames)) {
                            $value[$keyValue['key']] = $keyValue['value'];
                        }
                    }
                }
                return $value;
            }, $items);
        }
        if (null !== $uuid) {
            return !empty($items[0]) ? $items[0] : null;
        }
        return [
            'items' => $items,
            'pagination' => [
                'total_item_count' => $totalItems,
                'total_page_count' => $totalPages,
                'items_per_page' => $limit,
                'current_page' => $page,
                'current_page_items_count' => count($items),
                'has_next_page' => $page < $totalPages
            ]
        ];
    }

    /**
     * Find users by role name
     */
    public function findByRole(string $roleName): array
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.role', 'r')
            ->where('r.name = :roleName')
            ->andWhere('r.enabled = :enabled')
            ->andWhere('u.enabled = :enabled')
            ->setParameter('roleName', $roleName)
            ->setParameter('enabled', true)
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find users having any of the specified role names
     */
    public function findByAnyRole(array $roleNames): array
    {
        if (empty($roleNames)) {
            return [];
        }

        return $this->createQueryBuilder('u')
            ->innerJoin('u.role', 'r')
            ->where('r.name IN (:roleNames)')
            ->andWhere('r.enabled = :enabled')
            ->andWhere('u.enabled = :enabled')
            ->setParameter('roleNames', $roleNames)
            ->setParameter('enabled', true)
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get users with their role (includes role data)
     */
    public function findUsersWithRole(): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.role', 'r')
            ->addSelect('r')
            ->where('u.enabled = :enabled')
            ->setParameter('enabled', true)
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count users by role
     */
    public function countUsersByRole(string $roleName): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->innerJoin('u.role', 'r')
            ->where('r.name = :roleName')
            ->andWhere('r.enabled = :enabled')
            ->andWhere('u.enabled = :enabled')
            ->setParameter('roleName', $roleName)
            ->setParameter('enabled', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
