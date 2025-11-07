<?php

namespace App\Tenancy;

use Stancl\Tenancy\Contracts\TenantDatabaseManager;
use Stancl\Tenancy\Contracts\TenantWithDatabase;

class NullDatabaseManager implements TenantDatabaseManager
{
    public function createDatabase(TenantWithDatabase $tenant): bool
    {
        // Do nothing, just return true
        return true;
    }

    public function deleteDatabase(TenantWithDatabase $tenant): bool
    {
        // Do nothing, just return true
        return true;
    }

    public function databaseExists(string $name): bool
    {
        // Assume database exists
        return true;
    }

    /**
     * Stancl Tenancy v3 may require additional methods
     */
    public function makeConnectionConfig(array $baseConfig, string $databaseName): array
    {
        return array_merge($baseConfig, [
            'database' => $databaseName,
        ]);
    }

    public function setConnection(string $connection): void
    {
        // Do nothing
    }
}