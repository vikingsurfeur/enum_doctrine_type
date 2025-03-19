<?php

declare(strict_types=1);

namespace App\Tests\Integration\Doctrine;

use App\Doctrine\Type\TaskPriorityEnumType;
use App\Entity\Task;
use App\Enum\TaskPriorityEnum;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class EnumTypeIntegrationTest extends TestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        if (!Type::hasType(TaskPriorityEnumType::TYPE_NAME)) {
            Type::addType(TaskPriorityEnumType::TYPE_NAME, TaskPriorityEnumType::class);
        }

        // Configuration pour SQLite en mémoire
        $config = ORMSetup::createAttributeMetadataConfiguration(
            [__DIR__ . '/../../../src/Entity'],
            true
        );

        $config->setMetadataCache(new ArrayAdapter());
        $config->setQueryCache(new ArrayAdapter());

        // Utilisation de SQLite en mémoire pour les tests
        // Note: En production, vous utilisez PostgreSQL, mais pour les tests
        // SQLite est plus simple et plus rapide
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ], $config);

        // Remplacer la plateforme pour SQLite
        $platform = $connection->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping(TaskPriorityEnumType::TYPE_NAME, TaskPriorityEnumType::TYPE_NAME);

        $this->entityManager = new EntityManager($connection, $config);

        // Création du schéma
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($metadata);
    }

    public function testTaskPriorityEnumPersistenceAndRetrieval(): void
    {
        // Création d'une tâche
        $task = new Task();
        $task->setName('Test Task');
        $task->setCreatedAt(new \DateTimeImmutable());
        $task->setPriority(TaskPriorityEnum::HIGH);

        // Persistence
        $this->entityManager->persist($task);
        $this->entityManager->flush();
        $id = $task->getId();

        // Nettoyage du cache d'identité
        $this->entityManager->clear();

        // Récupération
        $retrievedTask = $this->entityManager->find(Task::class, $id);

        // Vérifications
        $this->assertNotNull($retrievedTask);
        $this->assertEquals('Test Task', $retrievedTask->getName());
        $this->assertEquals(TaskPriorityEnum::HIGH, $retrievedTask->getPriority());
        $this->assertInstanceOf(TaskPriorityEnum::class, $retrievedTask->getPriority());
    }

    public function testExceptionOnInvalidEnumValue(): void
    {
        // Test avec une requête SQL directe pour simuler une valeur invalide
        $this->expectException(\Exception::class);

        $conn = $this->entityManager->getConnection();
        $tableName = $this->entityManager->getClassMetadata(Task::class)->getTableName();

        $conn->executeStatement(
            "INSERT INTO {$tableName} (name, created_at, priority) VALUES (?, ?, ?)",
            ['Test Invalid', new \DateTime(), 'valeur_invalide']
        );
    }
}
