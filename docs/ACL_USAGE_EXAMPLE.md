# ACL System Usage Example

This document shows how to use the ACL entities provided by NxCoreBundle in your project (e.g., cosko-api).

## Overview

The NxCoreBundle provides `Role` and enhanced `User` entities with ACL support. The Role entity stores permissions as JSON arrays, allowing for flexible permission management. **Following YAGNI principles, only entities are provided - you implement the services and logic in your consuming application.**

## 1. Available Entities

### Role Entity
- `id` - Primary key
- `uuid` - UUID identifier  
- `name` - Role name (unique)
- `description` - Optional description
- `enabled` - Boolean flag
- `permissions` - JSON array of permission strings
- `created_at` / `updated_at` - Timestamps

### User Entity (Enhanced)
- All existing User properties
- `role` - ManyToOne relationship to Role entity
- Added methods: `getRole()`, `setRole()`, `hasRole()`

## 2. Define Permissions in Your Project

Create a Permission enum to define your application permissions:

```php
<?php
// src/Security/Permission.php in your project (cosko-api)
namespace App\Security;

enum Permission: string
{
    case CREATE_TECHNICIAN = 'create_technician';
    case EDIT_TECHNICIAN = 'edit_technician';
    case DELETE_TECHNICIAN = 'delete_technician';
    case VIEW_TECHNICIAN = 'view_technician';
    case MANAGE_WALLET = 'manage_wallet';
    case VIEW_WALLET = 'view_wallet';

    public function label(): string
    {
        return match($this) {
            self::CREATE_TECHNICIAN => 'Create Technician',
            self::EDIT_TECHNICIAN => 'Edit Technician', 
            self::DELETE_TECHNICIAN => 'Delete Technician',
            self::VIEW_TECHNICIAN => 'View Technician',
            self::MANAGE_WALLET => 'Manage Wallet',
            self::VIEW_WALLET => 'View Wallet',
        };
    }

    public static function getAllPermissions(): array
    {
        return array_map(fn(Permission $p) => $p->value, self::cases());
    }
}
```

## 3. Managing Roles and Permissions

### Creating Roles Programmatically

```php
<?php
// In a fixture or command
use OroMediaLab\NxCoreBundle\Entity\Role;
use App\Security\Permission;

// Create admin role with all permissions
$adminRole = new Role();
$adminRole->setName('admin')
    ->setDescription('Administrator with all permissions')
    ->setPermissions(Permission::getAllPermissions());

// Create manager role with specific permissions
$managerRole = new Role();
$managerRole->setName('manager')
    ->setDescription('Manager role') 
    ->setPermissions([
        Permission::CREATE_TECHNICIAN->value,
        Permission::EDIT_TECHNICIAN->value,
        Permission::VIEW_TECHNICIAN->value,
        Permission::MANAGE_WALLET->value,
    ]);

$entityManager->persist($adminRole);
$entityManager->persist($managerRole);
$entityManager->flush();
```

### API Endpoints for Role Management

#### POST /api/v1/roles - Create Role

```json
// Request
{
    "name": "supervisor",
    "description": "Supervisor role",
    "permissions": [
        "view_technician",
        "edit_technician",
        "view_wallet"
    ],
    "enabled": true
}

// Response
{
    "success": true,
    "data": {
        "id": 123,
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "name": "supervisor",
        "description": "Supervisor role",
        "permissions": [
            "view_technician",
            "edit_technician", 
            "view_wallet"
        ],
        "enabled": true,
        "created": "2024-01-01T12:00:00Z",
        "updated": "2024-01-01T12:00:00Z"
    }
}
```

#### PUT /api/v1/roles/{uuid} - Update Role

```json
// Request
{
    "description": "Updated supervisor role",
    "permissions": [
        "view_technician",
        "edit_technician",
        "delete_technician",
        "view_wallet",
        "manage_wallet"
    ],
    "enabled": true
}

// Response
{
    "success": true,
    "data": {
        "id": 123,
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "name": "supervisor",
        "description": "Updated supervisor role", 
        "permissions": [
            "view_technician",
            "edit_technician",
            "delete_technician",
            "view_wallet",
            "manage_wallet"
        ],
        "enabled": true,
        "created": "2024-01-01T12:00:00Z",
        "updated": "2024-01-01T15:30:00Z"
    }
}
```

#### PUT /api/v1/users/{uuid} - Assign Role to User

```json
// Request
{
    "name": "John Doe",
    "email_address": "john@example.com",
    "role_uuid": "550e8400-e29b-41d4-a716-446655440000",
    "enabled": true
}

// Response
{
    "success": true,
    "data": {
        "id": 456,
        "uuid": "123e4567-e89b-12d3-a456-426614174000",
        "username": "john_doe",
        "name": "John Doe",
        "email_address": "john@example.com",
        "enabled": true,
        "role": {
            "id": 123,
            "uuid": "550e8400-e29b-41d4-a716-446655440000",
            "name": "supervisor",
            "description": "Supervisor role",
            "enabled": true,
            "permissions": [
                "view_technician",
                "edit_technician",
                "view_wallet"
            ],
            "created": "2024-01-01T12:00:00Z",
            "updated": "2024-01-01T12:00:00Z"
        },
        "created": "2024-01-01T10:00:00Z"
    }
}
```

## 4. Implementing Permission Logic in Your Application

Since NxCoreBundle only provides entities, implement your own permission checking logic:

### Custom Permission Service

```php
<?php
// src/Service/PermissionService.php
namespace App\Service;

use OroMediaLab\NxCoreBundle\Entity\User;
use Symfony\Component\Security\Core\Security;

class PermissionService
{
    public function __construct(private Security $security)
    {
    }

    public function hasPermission(string $permission): bool
    {
        $user = $this->security->getUser();
        
        if (!$user instanceof User) {
            return false;
        }

        $role = $user->getRole(false);
        
        if (!$role || !$role->isEnabled() || !$user->isEnabled()) {
            return false;
        }

        return $role->hasPermission($permission);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }
}
```

### Using in Controllers

```php
<?php
// src/Controller/TechnicianController.php
namespace App\Controller;

use App\Security\Permission;
use App\Service\PermissionService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TechnicianController extends AbstractController
{
    public function __construct(private PermissionService $permissionService)
    {
    }

    public function create(): Response
    {
        if (!$this->permissionService->hasPermission(Permission::CREATE_TECHNICIAN->value)) {
            throw $this->createAccessDeniedException('Cannot create technicians');
        }

        // Your logic here
        return $this->json(['message' => 'Technician created']);
    }

    public function list(): Response
    {
        if (!$this->permissionService->hasPermission(Permission::VIEW_TECHNICIAN->value)) {
            throw $this->createAccessDeniedException('Cannot view technicians');
        }

        // Your logic here
        return $this->json(['technicians' => []]);
    }
}
```

## 5. Using Symfony Voters for Advanced Authorization

Create voters that integrate with your Role and Permission system for complex authorization:

### Permission-Based Voter

```php
<?php
// src/Security/Voter/PermissionVoter.php
namespace App\Security\Voter;

use App\Security\Permission;
use OroMediaLab\NxCoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PermissionVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        // Support all Permission enum values
        return in_array($attribute, Permission::getAllPermissions());
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        if (!$user instanceof User) {
            return false;
        }

        $role = $user->getRole(false);
        
        if (!$role || !$role->isEnabled() || !$user->isEnabled()) {
            return false;
        }

        // Use Role entity's hasPermission method
        return $role->hasPermission($attribute);
    }
}
```

### Using Voters in Controllers

```php
<?php
// src/Controller/TechnicianController.php
namespace App\Controller;

use App\Security\Permission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class TechnicianController extends AbstractController
{
    public function create(): Response
    {
        // Use Symfony's built-in authorization with your voter
        $this->denyAccessUnlessGranted(Permission::CREATE_TECHNICIAN->value);

        // Your logic here
        return $this->json(['message' => 'Technician created']);
    }

    public function edit(int $id): Response
    {
        $this->denyAccessUnlessGranted(Permission::EDIT_TECHNICIAN->value);
        
        // Your logic here
        return $this->json(['message' => 'Technician updated']);
    }

    public function delete(int $id): Response
    {
        $this->denyAccessUnlessGranted(Permission::DELETE_TECHNICIAN->value);
        
        // Your logic here
        return $this->json(['message' => 'Technician deleted']);
    }
}
```

## 6. Database Setup

Create and run migrations to add the Role table:

```bash
# Generate migration for the new Role entity
php bin/console make:migration

# Run migration
php bin/console doctrine:migrations:migrate
```

## 7. Summary

### What NxCoreBundle Provides:
- **Role Entity**: Stores roles with permissions as JSON arrays
- **Enhanced User Entity**: Added role relationship and related methods
- **UserRepository fetchAll**: Returns users with complete role information

### What You Implement in Your Application:
- **Permission Enum**: Define your application-specific permissions
- **Permission Services**: Create services for checking permissions
- **Voters**: Implement Symfony voters for complex authorization
- **API Controllers**: Build REST endpoints for role/permission management
- **Business Logic**: Add your specific authorization rules

### Key Benefits:
- **YAGNI Compliant**: Only provides essential entities, not unused services
- **Flexible**: JSON permission storage adapts to any permission structure
- **Symfony Integration**: Works seamlessly with Symfony's security system
- **Extensible**: Easy to extend with additional authorization logic

### Usage Pattern:
1. Define permissions in your app using enums
2. Create roles and assign permissions via API or fixtures
3. Use Role entity's `hasPermission()` method for basic checks
4. Implement voters for complex authorization logic
5. UserRepository's `fetchAll()` provides complete user+role data for admin interfaces

{# Display user's permissions for debugging or admin interface #}
{% if has_role('admin') %}
    <div class="debug-info">
        <h4>Current User Permissions:</h4>
        <ul>
        {% for permission in user_permissions() %}
            <li>{{ permission }}</li>
        {% endfor %}
        </ul>
    </div>
{% endif %}
```

## 5. Database Setup

Create and run migrations to add the permissions column:

```bash
# Generate migration for the new permissions column
php bin/console make:migration

# Run migration
php bin/console doctrine:migrations:migrate
```

## 6. Symfony Voters Integration

The ACL service provides data that Symfony Voters can use for complex authorization logic:

### Role Management Voter

First, create a voter for role management operations:

```php
<?php
// src/Security/Voter/RoleVoter.php
namespace App\Security\Voter;

use OroMediaLab\NxCoreBundle\Entity\Role;
use OroMediaLab\NxCoreBundle\Entity\User;
use OroMediaLab\NxCoreBundle\Service\AclService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RoleVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const MODIFY_PERMISSIONS = 'MODIFY_PERMISSIONS';
    public const DELETE = 'DELETE';
    public const CREATE = 'CREATE';

    public function __construct(
        private AclService $aclService
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::MODIFY_PERMISSIONS, self::DELETE, self::CREATE])
            && ($subject instanceof Role || $subject === null);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($user, $subject);
            case self::MODIFY_PERMISSIONS:
                return $this->canModifyPermissions($user, $subject);
            case self::DELETE:
                return $this->canDelete($user, $subject);
            case self::CREATE:
                return $this->canCreate($user);
        }

        return false;
    }

    private function canView(User $user, ?Role $role): bool
    {
        // Basic permission check using ACL service
        if ($this->aclService->hasRole('admin') || $this->aclService->hasRole('super_admin')) {
            return true;
        }

        // Managers can view roles but not modify them
        if ($this->aclService->hasRole('manager')) {
            return true;
        }

        return false;
    }

    private function canModifyPermissions(User $user, ?Role $role): bool
    {
        // Only admins can modify role permissions
        if ($this->aclService->hasRole('admin') || $this->aclService->hasRole('super_admin')) {
            return true;
        }

        // Additional business logic: prevent modifying admin roles
        if ($role && in_array(strtolower($role->getName()), ['admin', 'super_admin'])) {
            // Only super admins can modify admin roles
            return $this->aclService->hasRole('super_admin');
        }

        return false;
    }

    private function canDelete(User $user, ?Role $role): bool
    {
        // Only super admins can delete roles
        if (!$this->aclService->hasRole('super_admin')) {
            return false;
        }

        // Cannot delete system roles
        if ($role && in_array(strtolower($role->getName()), ['admin', 'super_admin'])) {
            return false;
        }

        return true;
    }

    private function canCreate(User $user): bool
    {
        // Admins and super admins can create new roles
        return $this->aclService->hasRole('admin') || $this->aclService->hasRole('super_admin');
    }
}
```

### Entity-Based Voter Example

```php
<?php
// src/Security/Voter/TechnicianVoter.php
namespace App\Security\Voter;

use App\Entity\Technician;
use App\Security\Permission;
use OroMediaLab\NxCoreBundle\Entity\User;
use OroMediaLab\NxCoreBundle\Service\AclService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TechnicianVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    public function __construct(
        private AclService $aclService
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Technician;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Technician $technician */
        $technician = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($user, $technician);
            case self::EDIT:
                return $this->canEdit($user, $technician);
            case self::DELETE:
                return $this->canDelete($user, $technician);
        }

        return false;
    }

    private function canView(User $user, Technician $technician): bool
    {
        // Use ACL service to check basic permission
        if (!$this->aclService->userHasPermission($user, Permission::VIEW_TECHNICIAN->value)) {
            return false;
        }

        // Additional business logic: users can always view their own profile
        if ($user->getId() === $technician->getUserId()) {
            return true;
        }

        // Managers can view all technicians in their department
        if ($this->aclService->hasRole('manager')) {
            return $technician->getDepartment() === $user->getDepartment();
        }

        return true;
    }

    private function canEdit(User $user, Technician $technician): bool
    {
        // Check basic edit permission using ACL
        if (!$this->aclService->userHasPermission($user, Permission::EDIT_TECHNICIAN->value)) {
            return false;
        }

        // Additional business logic
        return $this->canView($user, $technician);
    }

    private function canDelete(User $user, Technician $technician): bool
    {
        // Only admins can delete, regardless of other permissions
        return $this->aclService->hasRole('admin') && 
               $this->aclService->userHasPermission($user, Permission::DELETE_TECHNICIAN->value);
    }
}
```

## 7. Admin Interface Integration

For your admin interface, you can easily get available permissions and manage roles:

```php
<?php
// In your admin controller
public function getRolePermissions(Role $role): Response
{
    return $this->json([
        'role' => $role->getName(),
        'permissions' => $role->getPermissions(),
        'available_permissions' => Permission::getAllPermissions()
    ]);
}

public function updateRolePermissions(Role $role, Request $request): Response
{
    // Use Symfony Voter to check if user can modify this role
    $this->denyAccessUnlessGranted('MODIFY_PERMISSIONS', $role);
    
    $permissions = $request->request->get('permissions', []);
    
    // Validate permissions against available ones
    $availablePermissions = Permission::getAllPermissions();
    $validPermissions = array_intersect($permissions, $availablePermissions);
    
    $role->setPermissions($validPermissions);
    $this->entityManager->flush();
    
    return $this->json(['status' => 'success']);
}

public function addPermissionsToRole(Role $role, Request $request): Response
{
    // Use Symfony Voter to check if user can modify this role
    $this->denyAccessUnlessGranted('MODIFY_PERMISSIONS', $role);
    
    // Get permissions to add from POST request
    $permissionsToAdd = $request->request->get('permissions', []);
    
    // Validate against available permissions
    $availablePermissions = Permission::getAllPermissions();
    $validPermissionsToAdd = array_intersect($permissionsToAdd, $availablePermissions);
    
    // Get existing permissions
    $existingPermissions = $role->getPermissions();
    
    // Merge existing with new permissions (remove duplicates)
    $allPermissions = array_unique(array_merge($existingPermissions, $validPermissionsToAdd));
    
    // Set the combined permissions
    $role->setPermissions($allPermissions);
    $this->entityManager->flush();
    
    return $this->json([
        'status' => 'success',
        'added_permissions' => $validPermissionsToAdd,
        'total_permissions' => count($allPermissions),
        'current_permissions' => $allPermissions
    ]);
}

public function removePermissionsFromRole(Role $role, Request $request): Response
{
    // Use Symfony Voter to check if user can modify this role
    $this->denyAccessUnlessGranted('MODIFY_PERMISSIONS', $role);
    
    // Get permissions to remove from POST request
    $permissionsToRemove = $request->request->get('permissions', []);
    
    // Get existing permissions
    $existingPermissions = $role->getPermissions();
    
    // Remove specified permissions
    $remainingPermissions = array_diff($existingPermissions, $permissionsToRemove);
    
    // Reindex array to avoid gaps
    $remainingPermissions = array_values($remainingPermissions);
    
    // Set the filtered permissions
    $role->setPermissions($remainingPermissions);
    $this->entityManager->flush();
    
    return $this->json([
        'status' => 'success',
        'removed_permissions' => $permissionsToRemove,
        'remaining_permissions' => $remainingPermissions
    ]);
}

// Example with error handling and validation
public function addPermissionsToRoleWithValidation(Role $role, Request $request): Response
{
    try {
        // Use Symfony Voter to check if user can modify this role
        $this->denyAccessUnlessGranted('MODIFY_PERMISSIONS', $role);
        
        // Get permissions from request
        $permissionsToAdd = $request->request->get('permissions', []);
        
        // Validate input
        if (empty($permissionsToAdd) || !is_array($permissionsToAdd)) {
            return $this->json(['error' => 'No valid permissions provided'], 400);
        }
        
        // Validate against available permissions
        $availablePermissions = Permission::getAllPermissions();
        $validPermissions = array_intersect($permissionsToAdd, $availablePermissions);
        $invalidPermissions = array_diff($permissionsToAdd, $availablePermissions);
        
        // Check for invalid permissions
        if (!empty($invalidPermissions)) {
            return $this->json([
                'error' => 'Invalid permissions provided',
                'invalid_permissions' => $invalidPermissions,
                'available_permissions' => $availablePermissions
            ], 400);
        }
        
        // Get existing permissions
        $existingPermissions = $role->getPermissions();
        
        // Find permissions that are actually new (not already assigned)
        $newPermissions = array_diff($validPermissions, $existingPermissions);
        
        if (empty($newPermissions)) {
            return $this->json([
                'message' => 'All specified permissions already exist for this role',
                'current_permissions' => $existingPermissions
            ]);
        }
        
        // Merge existing with new permissions
        $allPermissions = array_unique(array_merge($existingPermissions, $validPermissions));
        
        // Set permissions
        $role->setPermissions($allPermissions);
        $this->entityManager->flush();
        
        return $this->json([
            'status' => 'success',
            'role' => $role->getName(),
            'added_permissions' => array_values($newPermissions),
            'total_permissions_count' => count($allPermissions),
            'current_permissions' => $allPermissions
        ]);
        
    } catch (\Exception $e) {
        return $this->json(['error' => 'Failed to update role permissions: ' . $e->getMessage()], 500);
    }
}
```

### API Request/Response Examples:

**1. Add Permissions to Role:**

```bash
# POST /api/roles/{roleId}/add-permissions
curl -X POST /api/roles/123/add-permissions \
  -H "Content-Type: application/json" \
  -d '{
    "permissions": [
      "delete_technician",
      "manage_wallet"
    ]
  }'
```

**Response (Success):**
```json
{
  "status": "success",
  "role": "manager",
  "added_permissions": ["delete_technician", "manage_wallet"],
  "total_permissions_count": 7,
  "current_permissions": [
    "view_technician",
    "edit_technician",
    "create_technician",
    "view_leads",
    "manage_leads",
    "delete_technician",
    "manage_wallet"
  ]
}
```

**2. Remove Permissions from Role:**

```bash
# POST /api/roles/{roleId}/remove-permissions  
curl -X POST /api/roles/123/remove-permissions \
  -H "Content-Type: application/json" \
  -d '{
    "permissions": [
      "delete_technician",
      "manage_wallet"
    ]
  }'
```

**Response (Success):**
```json
{
  "status": "success",
  "removed_permissions": ["delete_technician", "manage_wallet"],
  "remaining_permissions": [
    "view_technician",
    "edit_technician",
    "create_technician",
    "view_leads",
    "manage_leads"
  ]
}
```

**3. Replace All Permissions:**

```bash
# PUT /api/roles/{roleId}/permissions
curl -X PUT /api/roles/123/permissions \
  -H "Content-Type: application/json" \
  -d '{
    "permissions": [
      "view_technician",
      "edit_technician"
    ]
  }'
```

**Response (Error - Invalid Permission):**
```json
{
  "error": "Invalid permissions provided",
  "invalid_permissions": ["invalid_permission_name"],
  "available_permissions": [
    "create_technician",
    "edit_technician",
    "delete_technician",
    "view_technician",
    "manage_wallet",
    "view_wallet",
    "manage_leads",
    "view_leads",
    "assign_leads"
  ]
}
```

**Response (Error - Access Denied by Voter):**
```json
{
  "error": "Access denied",
  "message": "You do not have permission to modify this role",
  "code": 403
}
```

### Additional Controller Examples with Voters:

```php
<?php
// Additional examples showing different voter checks

public function getRolePermissions(Role $role): Response
{
    // Check if user can view this role
    $this->denyAccessUnlessGranted('VIEW', $role);
    
    return $this->json([
        'role' => $role->getName(),
        'permissions' => $role->getPermissions(),
        'available_permissions' => Permission::getAllPermissions()
    ]);
}

public function createRole(Request $request): Response
{
    // Check if user can create roles
    $this->denyAccessUnlessGranted('CREATE', Role::class);
    
    $roleName = $request->request->get('name');
    $permissions = $request->request->get('permissions', []);
    
    // Validate permissions
    $availablePermissions = Permission::getAllPermissions();
    $validPermissions = array_intersect($permissions, $availablePermissions);
    
    $role = new Role();
    $role->setName($roleName)
         ->setPermissions($validPermissions);
    
    $this->entityManager->persist($role);
    $this->entityManager->flush();
    
    return $this->json([
        'status' => 'success',
        'role' => [
            'id' => $role->getId(),
            'name' => $role->getName(),
            'permissions' => $role->getPermissions()
        ]
    ], 201);
}

public function deleteRole(Role $role): Response
{
    // Check if user can delete this specific role
    $this->denyAccessUnlessGranted('DELETE', $role);
    
    $roleName = $role->getName();
    
    $this->entityManager->remove($role);
    $this->entityManager->flush();
    
    return $this->json([
        'status' => 'success',
        'message' => "Role '{$roleName}' has been deleted"
    ]);
}

// Example with manual voter check (alternative to denyAccessUnlessGranted)
public function bulkUpdateRoles(Request $request): Response
{
    $roleUpdates = $request->request->get('roles', []);
    $results = [];
    
    foreach ($roleUpdates as $roleData) {
        $role = $this->roleRepository->find($roleData['id']);
        
        if (!$role) {
            $results[] = ['id' => $roleData['id'], 'status' => 'error', 'message' => 'Role not found'];
            continue;
        }
        
        // Manual voter check with custom handling
        if (!$this->isGranted('MODIFY_PERMISSIONS', $role)) {
            $results[] = [
                'id' => $role->getId(), 
                'status' => 'error', 
                'message' => 'Access denied for role: ' . $role->getName()
            ];
            continue;
        }
        
        // Update permissions
        if (isset($roleData['permissions'])) {
            $validPermissions = array_intersect($roleData['permissions'], Permission::getAllPermissions());
            $role->setPermissions($validPermissions);
            
            $results[] = [
                'id' => $role->getId(),
                'status' => 'success',
                'role' => $role->getName(),
                'updated_permissions' => $validPermissions
            ];
        }
    }
    
    $this->entityManager->flush();
    
    return $this->json([
        'status' => 'completed',
        'results' => $results
    ]);
}
```

This setup gives you:
- **Database-driven permissions** stored directly in Role entities
- **No hardcoded permission mapping** - all dynamic through admin interface
- **Type-safe permissions** via enums in your project
- **Symfony Voters integration** for complex authorization logic
- **Easy permission checking** in controllers and templates
- **Clean separation** between bundle and project-specific logic
- **Flexible role management** through admin interfaces
