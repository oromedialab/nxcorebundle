<?php

declare(strict_types=1);

namespace OroMediaLab\NxCoreBundle\Service;

use OroMediaLab\NxCoreBundle\Entity\User;
use OroMediaLab\NxCoreBundle\Repository\RoleRepository;
use OroMediaLab\NxCoreBundle\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AclService
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private UserRepository $userRepository,
        private RoleRepository $roleRepository
    ) {
    }

    /**
     * Check if current user has permission
     * 
     * @param string $permission Permission key (e.g., from project enum)
     */
    public function hasPermission(string $permission): bool
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            return false;
        }

        return $this->userHasPermission($user, $permission);
    }

    /**
     * Check if specific user has permission
     * 
     * @param User $user
     * @param string $permission Permission key (e.g., from project enum)
     */
    public function userHasPermission(User $user, string $permission): bool
    {
        if (!$user->isEnabled()) {
            return false;
        }

        $userRole = $user->getRole(false); // Get Role object, not string
        
        if (!$userRole || !$userRole->isEnabled()) {
            return false;
        }
        
        // Check if role has the specific permission
        return $userRole->hasPermission($permission);
    }

    /**
     * Check if current user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            return false;
        }
        
        $userRole = $user->getRole(false);
        
        if (!$userRole || !$userRole->isEnabled()) {
            return false;
        }
        
        // Use Role's hasPermission with array - more efficient single call
        $results = $userRole->hasPermission($permissions);
        return in_array(true, $results);
    }

    /**
     * Check if current user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            return false;
        }
        
        $userRole = $user->getRole(false);
        
        if (!$userRole || !$userRole->isEnabled()) {
            return false;
        }
        
        // Use Role's hasPermission with array - more efficient single call
        $results = $userRole->hasPermission($permissions);
        return !in_array(false, $results);
    }

    /**
     * Get permission check results for multiple permissions
     * 
     * @param array $permissions Array of permission strings
     * @return array Array of boolean results corresponding to each permission
     */
    public function getPermissionResults(array $permissions): array
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            return array_fill(0, count($permissions), false);
        }
        
        $userRole = $user->getRole(false);
        
        if (!$userRole || !$userRole->isEnabled()) {
            return array_fill(0, count($permissions), false);
        }
        
        return $userRole->hasPermission($permissions);
    }

    /**
     * Check if current user has specific role
     */
    public function hasRole(string $roleName): bool
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            return false;
        }

        return $user->hasRole($roleName);
    }

    /**
     * Check if current user has any of the given roles
     */
    public function hasAnyRole(array $roleNames): bool
    {
        foreach ($roleNames as $roleName) {
            if ($this->hasRole($roleName)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get current authenticated user
     */
    public function getCurrentUser(): ?User
    {
        $token = $this->tokenStorage->getToken();
        
        if (!$token) {
            return null;
        }

        $user = $token->getUser();

        return $user instanceof User ? $user : null;
    }

    /**
     * Get all permissions for current user based on their role
     */
    public function getCurrentUserPermissions(): array
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            return [];
        }

        return $this->getUserPermissions($user);
    }

    /**
     * Get all permissions for specific user based on their role
     * 
     * @param User $user
     */
    public function getUserPermissions(User $user): array
    {
        if (!$user->isEnabled()) {
            return [];
        }

        $userRole = $user->getRole(false); // Get Role object, not string
        
        if (!$userRole || !$userRole->isEnabled()) {
            return [];
        }

        return $userRole->getPermissions();
    }

    /**
     * Check if current user is admin (has admin-like role)
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['admin', 'super_admin', 'administrator']);
    }

    /**
     * Deny access if current user doesn't have permission
     * 
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function denyAccessUnlessGranted(string $permission): void
    {
        if (!$this->hasPermission($permission)) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException(
                sprintf('Access denied. Required permission: %s', $permission)
            );
        }
    }
}