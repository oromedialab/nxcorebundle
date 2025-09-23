<?php

declare(strict_types=1);

namespace OroMediaLab\NxCoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use OroMediaLab\NxCoreBundle\Entity\Role;

/**
 * @extends ServiceEntityRepository<Role>
 *
 * @method Role|null find($id, $lockMode = null, $lockVersion = null)
 * @method Role|null findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findAll()
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    public function save(Role $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Role $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find all enabled roles
     */
    public function findEnabledRoles(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.enabled = :enabled')
            ->setParameter('enabled', true)
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find role by name (case insensitive)
     */
    public function findByName(string $name): ?Role
    {
        return $this->createQueryBuilder('r')
            ->where('LOWER(r.name) = LOWER(:name)')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find roles by names array
     */
    public function findByNames(array $names): array
    {
        if (empty($names)) {
            return [];
        }

        return $this->createQueryBuilder('r')
            ->where('r.name IN (:names)')
            ->andWhere('r.enabled = :enabled')
            ->setParameter('names', $names)
            ->setParameter('enabled', true)
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get roles with user count
     */
    public function findRolesWithUserCount(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r', 'COUNT(u.id) as userCount')
            ->leftJoin('OroMediaLab\\NxCoreBundle\\Entity\\User', 'u', 'WITH', 'u.role = r AND u.enabled = true')
            ->groupBy('r.id')
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Check if role name exists (for unique validation)
     */
    public function roleNameExists(string $name, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('LOWER(r.name) = LOWER(:name)')
            ->setParameter('name', $name);

        if ($excludeId !== null) {
            $qb->andWhere('r.id != :excludeId')
               ->setParameter('excludeId', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}