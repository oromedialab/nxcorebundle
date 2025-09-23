# ACL System Usage Example

This document shows how to use the ACL system in your project (e.g., cosko-api).

## Overview

The ACL system now stores permissions directly in the database within the `Role` entity. This allows for dynamic permission management where admins can assign specific permissions to roles through your admin interface.

## 1. Define Permissions Enum in Your Project

Create a Permission enum in your project to define all available permissions:

```php
<?php
// src/Security/Permission.php in your project (cosko-api)
namespace App\Security;

enum Permission: string
{
    // Technician management
    case CREATE_TECHNICIAN = 'create_technician';
    case EDIT_TECHNICIAN = 'edit_technician';
    case DELETE_TECHNICIAN = 'delete_technician';
    case VIEW_TECHNICIAN = 'view_technician';

    // Wallet management
    case MANAGE_WALLET = 'manage_wallet';
    case VIEW_WALLET = 'view_wallet';

    // Leads management
    case MANAGE_LEADS = 'manage_leads';
    case VIEW_LEADS = 'view_leads';
    case ASSIGN_LEADS = 'assign_leads';

    public function key(): string
    {
        return $this->value;
    }

    public function name(): string
    {
        return match($this) {
            self::CREATE_TECHNICIAN => 'Create Technician',
            self::EDIT_TECHNICIAN => 'Edit Technician',
            self::DELETE_TECHNICIAN => 'Delete Technician',
            self::VIEW_TECHNICIAN => 'View Technician',
            self::MANAGE_WALLET => 'Manage Wallet',
            self::VIEW_WALLET => 'View Wallet',
            self::MANAGE_LEADS => 'Manage Leads',
            self::VIEW_LEADS => 'View Leads',
            self::ASSIGN_LEADS => 'Assign Leads',
        };
    }

    public function group(): string
    {
        return match(true) {
            str_starts_with($this->value, 'create_technician') => 'Technician Management',
            str_starts_with($this->value, 'edit_technician') => 'Technician Management',
            str_starts_with($this->value, 'delete_technician') => 'Technician Management',
            str_starts_with($this->value, 'view_technician') => 'Technician Management',
            str_contains($this->value, 'wallet') => 'Wallet Management',
            str_contains($this->value, 'leads') => 'Leads Management',
            default => 'Other',
        };
    }

    public function description(): string
    {
        return '';
    }

    /**
     * Get all available permissions as array (useful for admin interfaces)
     */
    public static function getAllPermissions(): array
    {
        return array_map(fn(Permission $p) => $p->value, self::cases());
    }
}
```

## 2. Managing Role Permissions

With the new system, permissions are stored directly in the database within the `Role` entity. You can manage them through your admin interface or programmatically:

### Programmatic Permission Management:

```php
<?php
// Example: Setting up roles with permissions in a command or fixture

use OroMediaLab\NxCoreBundle\Entity\Role;
use App\Security\Permission;

// Create roles with permissions
$adminRole = new Role();
$adminRole->setName('admin')
    ->setDescription('Administrator role')
    ->setPermissions(Permission::getAllPermissions()); // All permissions

$managerRole = new Role();
$managerRole->setName('manager')
    ->setDescription('Manager role')
    ->setPermissions([
        Permission::CREATE_TECHNICIAN->value,
        Permission::EDIT_TECHNICIAN->value,
        Permission::VIEW_TECHNICIAN->value,
        Permission::MANAGE_LEADS->value,
        Permission::VIEW_LEADS->value,
        Permission::ASSIGN_LEADS->value,
        Permission::VIEW_WALLET->value,
    ]);

$technicianRole = new Role();
$technicianRole->setName('technician')
    ->setDescription('Technician role')
    ->setPermissions([
        Permission::VIEW_TECHNICIAN->value,
        Permission::VIEW_LEADS->value,
        Permission::VIEW_WALLET->value,
    ]);

// Save roles
$entityManager->persist($adminRole);
$entityManager->persist($managerRole);
$entityManager->persist($technicianRole);
$entityManager->flush();
```

### Dynamic Permission Management:

```php
<?php
// Adding/removing permissions from roles dynamically

// Set permissions for a role (replaces existing permissions)
$role = $roleRepository->findOneBy(['name' => 'manager']);
$role->setPermissions([
    Permission::VIEW_TECHNICIAN->value,
    Permission::EDIT_TECHNICIAN->value,
    Permission::MANAGE_LEADS->value,
]);
$entityManager->flush();

// Add more permissions to existing ones
$existingPermissions = $role->getPermissions();
$newPermissions = array_merge($existingPermissions, [
    Permission::DELETE_TECHNICIAN->value,
    Permission::MANAGE_WALLET->value,
]);
$role->setPermissions(array_unique($newPermissions));
$entityManager->flush();

// Remove specific permissions
$existingPermissions = $role->getPermissions();
$permissionsToRemove = [
    Permission::DELETE_TECHNICIAN->value,
    Permission::MANAGE_WALLET->value,
];
$filteredPermissions = array_diff($existingPermissions, $permissionsToRemove);
$role->setPermissions(array_values($filteredPermissions));
$entityManager->flush();

// Check if role has a specific permission
if ($role->hasPermission(Permission::CREATE_TECHNICIAN->value)) {
    // Role has this permission
}

// Check multiple permissions at once - returns array of bool results
$permissionResults = $role->hasPermission([
    Permission::EDIT_TECHNICIAN->value,
    Permission::DELETE_TECHNICIAN->value,
    Permission::VIEW_TECHNICIAN->value,
]);
// $permissionResults = [true, false, true] for example

// Check if role has any of the given permissions
$checkPermissions = [
    Permission::EDIT_TECHNICIAN->value,
    Permission::DELETE_TECHNICIAN->value,
];
$results = $role->hasPermission($checkPermissions);
if (in_array(true, $results)) {
    // Role has at least one of these permissions
}

// Check if role has all of the given permissions
$checkPermissions = [
    Permission::VIEW_TECHNICIAN->value,
    Permission::EDIT_TECHNICIAN->value,
];
$results = $role->hasPermission($checkPermissions);
if (!in_array(false, $results)) {
    // Role has all of these permissions
}
```

## 3. Usage in Controllers

```php
<?php
// In your controller
namespace App\Controller;

use App\Security\Permission;
use OroMediaLab\NxCoreBundle\Service\AclService;
use Symfony\Component\HttpFoundation\Response;

class TechnicianController extends AbstractController
{
    public function __construct(
        private AclService $aclService
    ) {
    }

    public function createTechnician(): Response
    {
        // Method 1: Check permission manually
        if (!$this->aclService->hasPermission(Permission::CREATE_TECHNICIAN->value)) {
            throw $this->createAccessDeniedException('You cannot create technicians');
        }

        // Method 2: Using denyAccessUnlessGranted (throws exception automatically)
        $this->aclService->denyAccessUnlessGranted(Permission::CREATE_TECHNICIAN->value);

        // Your logic here
        return new Response('Technician created');
    }

    public function listTechnicians(): Response
    {
        // Check permission
        $this->aclService->denyAccessUnlessGranted(Permission::VIEW_TECHNICIAN->value);

        // Your logic here
        return new Response('Technician list');
    }

    public function manageTechnician(): Response
    {
        // Check multiple permissions (user needs ANY of these)
        if (!$this->aclService->hasAnyPermission([
            Permission::EDIT_TECHNICIAN->value,
            Permission::DELETE_TECHNICIAN->value,
        ])) {
            throw $this->createAccessDeniedException('Insufficient permissions');
        }

        // Check multiple permissions (user needs ALL of these)
        if (!$this->aclService->hasAllPermissions([
            Permission::VIEW_TECHNICIAN->value,
            Permission::EDIT_TECHNICIAN->value,
        ])) {
            throw $this->createAccessDeniedException('You need both view and edit permissions');
        }

        // Your logic here
        return new Response('Manage technician');
    }

    public function getUserPermissions(): Response
    {
        // Get all permissions for current user
        $permissions = $this->aclService->getCurrentUserPermissions();
        
        return $this->json(['permissions' => $permissions]);
    }

    public function checkMultiplePermissions(): Response
    {
        // Check multiple permissions at once - get detailed results
        $permissionsToCheck = [
            Permission::CREATE_TECHNICIAN->value,
            Permission::EDIT_TECHNICIAN->value,
            Permission::DELETE_TECHNICIAN->value,
            Permission::VIEW_WALLET->value,
        ];
        
        $results = $this->aclService->getPermissionResults($permissionsToCheck);
        
        // $results = [true, true, false, false] for example
        return $this->json([
            'permission_results' => array_combine($permissionsToCheck, $results)
        ]);
    }
}
```

## 4. Usage in Twig Templates

Register the AclService as accessible in Twig:

```yaml
# config/services.yaml in your project
services:
    # ... other services

    # Make AclService available in Twig
    app.twig_extension.acl:
        class: App\Twig\AclExtension
        arguments:
            - '@nxcore.service.acl'
        tags:
            - { name: twig.extension }
```

```php
<?php
// src/Twig/AclExtension.php
namespace App\Twig;

use OroMediaLab\NxCoreBundle\Service\AclService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AclExtension extends AbstractExtension
{
    public function __construct(
        private AclService $aclService
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('has_permission', [$this, 'hasPermission']),
            new TwigFunction('has_any_permission', [$this, 'hasAnyPermission']),
            new TwigFunction('has_all_permissions', [$this, 'hasAllPermissions']),
            new TwigFunction('has_role', [$this, 'hasRole']),
            new TwigFunction('user_permissions', [$this, 'getUserPermissions']),
        ];
    }

    public function hasPermission(string $permission): bool
    {
        return $this->aclService->hasPermission($permission);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        return $this->aclService->hasAnyPermission($permissions);
    }

    public function hasAllPermissions(array $permissions): bool
    {
        return $this->aclService->hasAllPermissions($permissions);
    }

    public function hasRole(string $role): bool
    {
        return $this->aclService->hasRole($role);
    }

    public function getUserPermissions(): array
    {
        return $this->aclService->getCurrentUserPermissions();
    }
}
```

Then in Twig:

```twig
{# templates/technician/list.html.twig #}
{% if has_permission('create_technician') %}
    <a href="{{ path('technician_create') }}" class="btn btn-primary">
        Create Technician
    </a>
{% endif %}

{# Check multiple permissions - user needs ANY #}
{% if has_any_permission(['edit_technician', 'delete_technician']) %}
    <div class="management-tools">
        <!-- Show management tools -->
    </div>
{% endif %}

{# Check multiple permissions - user needs ALL #}
{% if has_all_permissions(['view_technician', 'edit_technician']) %}
    <a href="{{ path('technician_edit') }}" class="btn btn-warning">
        Edit Technician
    </a>
{% endif %}

{% if has_role('admin') %}
    <div class="admin-panel">
        <!-- Admin-only content -->
    </div>
{% endif %}

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
