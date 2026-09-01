<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasRbacPermission
{
    protected static ?string $rbacGroup = null;

    protected static ?string $rbacResource = null;

    protected static function rbacAction(string $action): string
    {
        return match ($action) {
            'viewAny', 'view' => 'view',
            'create' => 'create',
            'update', 'edit' => 'edit',
            'delete', 'deleteAny', 'forceDelete', 'forceDeleteAny' => 'delete',
            default => $action,
        };
    }

    public static function getRbacGroupLabel(): string
    {
        if (static::$rbacGroup) {
            return static::$rbacGroup;
        }

        if (method_exists(static::class, 'getNavigationGroup') && filled(static::getNavigationGroup())) {
            return (string) static::getNavigationGroup();
        }

        if (method_exists(static::class, 'getCluster') && static::getCluster()) {
            $clusterClass = static::getCluster();
            $label = method_exists($clusterClass, 'getNavigationLabel')
                ? $clusterClass::getNavigationLabel()
                : class_basename($clusterClass);

            return strval($label ?: class_basename($clusterClass));
        }

        return class_basename(static::class);
    }

    public static function getRbacGroup(): string
    {
        return Str::slug(static::getRbacGroupLabel());
    }

    public static function getRbacResource(): string
    {
        $slug = static::getSlug();

        return Str::slug($slug ?: class_basename(static::getModel()));
    }

    public static function getRbacPermissionNames(): array
    {
        return [
            'view' => static::getRbacGroup().'.'.static::getRbacResource().'.view',
            'create' => static::getRbacGroup().'.'.static::getRbacResource().'.create',
            'edit' => static::getRbacGroup().'.'.static::getRbacResource().'.edit',
            'delete' => static::getRbacGroup().'.'.static::getRbacResource().'.delete',
        ];
    }

    protected static function rbacPermission(string $action): string
    {
        return static::getRbacGroup().'.'.static::getRbacResource().'.'.static::rbacAction($action);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canAccess(static::rbacPermission('viewAny')) ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->canAccess(static::rbacPermission('view')) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canAccess(static::rbacPermission('create')) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->canAccess(static::rbacPermission('edit')) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->canAccess(static::rbacPermission('delete')) ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->canAccess(static::rbacPermission('deleteAny')) ?? false;
    }

    public static function canForceDelete(Model $record): bool
    {
        return auth()->user()?->canAccess(static::rbacPermission('forceDelete')) ?? false;
    }

    public static function canForceDeleteAny(): bool
    {
        return auth()->user()?->canAccess(static::rbacPermission('forceDeleteAny')) ?? false;
    }

    public static function canRestore(Model $record): bool
    {
        return auth()->user()?->canAccess(static::rbacPermission('restore')) ?? false;
    }
}
