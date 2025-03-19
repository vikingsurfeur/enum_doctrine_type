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

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        $enumClass = static::getEnumClass();

        // Utiliser la méthode tryFrom pour les backed enums
        if (method_exists($enumClass, 'tryFrom')) {
            $result = $enumClass::tryFrom($value);
            if ($result === null) {
                throw new \InvalidArgumentException(
                    sprintf('La valeur "%s" n\'est pas valide pour l\'enum %s', $value, $enumClass)
                );
            }
            return $result;
        }

        // Pour les enums non-backed, parcourir les cas
        foreach ($enumClass::cases() as $case) {
            if ($case->name === $value) {
                return $case;
            }
        }

        throw new \InvalidArgumentException(
            sprintf('La valeur "%s" n\'est pas valide pour l\'enum %s', $value, $enumClass)
        );
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
