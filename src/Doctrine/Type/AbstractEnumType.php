<?php

declare(strict_types=1);

namespace App\Doctrine\Type;

use App\Doctrine\Helper\EnumTypeHelper;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Base class for all enum types
 * 
 * Les classes dérivées doivent implémenter getEnumClass() et getTypeName()
 */
abstract class AbstractEnumType extends Type
{
    /**
     * Get the associated PHP enum class
     * 
     * @return string Fully qualified class name of the enum
     */
    abstract protected static function getEnumClass(): string;

    /**
     * Get the database type name for the enum
     */
    abstract public static function getTypeName(): string;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return static::getTypeName();
    }

    /**
     * Convertit la valeur PHP en valeur pour la base de données
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        // Si la valeur a une propriété 'value', c'est un backed enum
        if (is_object($value) && property_exists($value, 'value')) {
            return (string)$value->value;
        }

        // Sinon, on la convertit en chaîne
        return (string)$value;
    }

    /**
     * Convertit la valeur de la base de données en objet enum PHP
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        $enumClass = static::getEnumClass();

        // Utiliser la méthode tryFrom si elle existe
        if (method_exists($enumClass, 'tryFrom')) {
            return $enumClass::tryFrom($value);
        }

        // Si tryFrom n'existe pas, essayer from
        if (method_exists($enumClass, 'from')) {
            return $enumClass::from($value);
        }

        // Si aucune méthode n'est disponible, parcourir les cas
        foreach ($enumClass::cases() as $case) {
            if ($case->name === $value) {
                return $case;
            }
        }

        return null;
    }

    public function getName(): string
    {
        return static::getTypeName();
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    /**
     * Génère le SQL pour créer le type ENUM PostgreSQL
     */
    public static function getCreateTypeSQL(): string
    {
        return EnumTypeHelper::getCreateEnumTypeSQL(
            static::getTypeName(),
            static::getEnumClass()
        );
    }

    /**
     * Génère le SQL pour supprimer le type ENUM PostgreSQL
     */
    public static function getDropTypeSQL(): string
    {
        return EnumTypeHelper::getDropEnumTypeSQL(static::getTypeName());
    }
}
