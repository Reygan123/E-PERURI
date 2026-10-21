<?php

namespace App\Modules\Platform\Contracts;

interface DirectoryServiceInterface
{
    /**
     * Get active employee details by employee UUID.
     *
     * @return array<string, mixed>|null
     */
    public function getEmployeeById(string $employeeId): ?array;

    /**
     * Get active employee details by NIP.
     *
     * @return array<string, mixed>|null
     */
    public function getEmployeeByNip(string $nip): ?array;

    /**
     * Get organizational unit details by UUID or code.
     *
     * @return array<string, mixed>|null
     */
    public function getOrgUnit(string $idOrCode): ?array;

    /**
     * Get all subordinate organizational unit IDs for a given parent unit ID.
     *
     * @return list<string>
     */
    public function getSubordinateOrgUnitIds(string $orgUnitId): array;

    /**
     * Check if an employee belongs to an organizational unit or its descendants.
     */
    public function isEmployeeInOrgUnit(string $employeeId, string $orgUnitId, bool $recursive = true): bool;
}
