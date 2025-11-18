<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;

class DatabaseHelper
{
    /**
     * Get the current unit database connection name.
     *
     * @return string|null
     */
    public static function getCurrentUnitConnection(): ?string
    {
        return session('current_unit_connection') ?? Config::get('database.default_unit_connection');
    }

    /**
     * Get the current unit ID.
     *
     * @return int|null
     */
    public static function getCurrentUnitId(): ?int
    {
        return session('current_unit_id');
    }

    /**
     * Get the current company ID.
     *
     * @return int|null
     */
    public static function getCurrentCompanyId(): ?int
    {
        return session('current_company_id');
    }

    /**
     * Set the current unit.
     *
     * @param int $unitId
     * @param string $unitName
     * @param string $connection
     * @return void
     */
    public static function setCurrentUnit(int $unitId, string $unitName, string $connection): void
    {
        session([
            'current_unit_id' => $unitId,
            'current_unit_name' => $unitName,
            'current_unit_connection' => $connection,
        ]);

        Config::set('database.default_unit_connection', $connection);
    }

    /**
     * Set the current company.
     *
     * @param int $companyId
     * @return void
     */
    public static function setCurrentCompany(int $companyId): void
    {
        session(['current_company_id' => $companyId]);
    }

    /**
     * Clear the current unit and company.
     *
     * @return void
     */
    public static function clearCurrent(): void
    {
        session()->forget(['current_unit_id', 'current_unit_name', 'current_unit_connection', 'current_company_id']);
        Config::set('database.default_unit_connection', null);
    }

    /**
     * Get a model instance with the current unit connection.
     *
     * @param string $modelClass
     * @return mixed
     */
    public static function model(string $modelClass)
    {
        $connection = self::getCurrentUnitConnection();
        
        if (!$connection) {
            throw new \Exception('لم يتم اختيار وحدة. يرجى اختيار وحدة أولاً.');
        }

        $model = new $modelClass();
        $model->setConnection($connection);
        
        return $model;
    }
}
