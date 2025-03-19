<?php

declare(strict_types=1);

namespace App\Doctrine\Helper;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;

/**
 * Helper class for working with Enum types in Doctrine
 */
class EnumTypeHelper
{
    /**
     * Generates SQL for creating a PostgreSQL enum type from a PHP enum
     *
     * @param string $typeName The name to give to the PostgreSQL enum type
     * @param string $enumClass The fully qualified class name of the PHP enum
     * @return string SQL statement to create the enum type
     */
    public static function getCreateEnumTypeSQL(string $typeName, string $enumClass): string
    {
        // Vérifier que la classe existe
        if (!class_exists($enumClass)) {
            throw new \InvalidArgumentException("$enumClass n'existe pas");
        }

        // Récupérer toutes les valeurs de l'enum
        $values = [];
        $cases = $enumClass::cases();

        foreach ($cases as $case) {
            // Si l'enum a une méthode ou propriété 'value', c'est un backed enum
            if (property_exists($case, 'value')) {
                $values[] = $case->value;
            } else {
                $values[] = $case->name;
            }
        }

        // S'assurer que nous avons des valeurs
        if (empty($values)) {
            throw new \InvalidArgumentException("L'enum $enumClass ne contient aucune valeur");
        }

        // Construire la requête SQL
        $quotedValues = array_map(function ($value) {
            return "'" . addslashes((string)$value) . "'";
        }, $values);

        return sprintf('CREATE TYPE %s AS ENUM (%s)', $typeName, implode(', ', $quotedValues));
    }

    /**
     * Gets the appropriate SQL declaration for an enum column
     *
     * @param string $enumTypeName The PostgreSQL enum type name
     * @param AbstractPlatform $platform The database platform
     * @return string
     */
    public static function getEnumTypeDeclarationSQL(string $enumTypeName, AbstractPlatform $platform): string
    {
        if ($platform instanceof PostgreSQLPlatform) {
            // With PostgreSQL, we can use the custom enum type directly
            return $enumTypeName;
        }

        // For other platforms, we'd use a regular string type
        return $platform->getStringTypeDeclarationSQL([]);
    }

    /**
     * Generates SQL for dropping a PostgreSQL enum type
     *
     * @param string $typeName The name of the PostgreSQL enum type
     * @return string SQL statement to drop the enum type
     */
    public static function getDropEnumTypeSQL(string $typeName): string
    {
        return sprintf('DROP TYPE IF EXISTS %s', $typeName);
    }
}
