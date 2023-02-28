<?php

namespace OroMediaLab\NxCoreBundle\Repository;

use OroMediaLab\NxCoreBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
        foreach ($paginator as $row) {
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
}
