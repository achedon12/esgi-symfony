<?php

namespace Tests;

use App\DataFixtures\AppFixtures;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class DatabaseTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testDatabaseConnection()
    {
        $connection = $this->entityManager->getConnection();

        // database creation
        $params = $connection->getParams();
        if (isset($params['path']) && !file_exists($params['path'])) {
            $schemaTool = new SchemaTool($this->entityManager);
            $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

            if (!empty($metadata)) {
                $schemaTool->createSchema($metadata);
            }
        }

        // database connection
        $connection->connect();

        $this->assertTrue($connection->isConnected(), 'La connexion à la base de données a échoué.');

        if (isset($params['path'])) {
            $this->assertFileExists($params['path'], 'Le fichier de la base de données SQLite n\'a pas été créé.');
        }
    }

    public function seedDatabaseTest(): void
    {
        $this->loadFixtures();
    }

    private function loadFixtures(): void
    {
        $hasher = self::$kernel->getContainer()->get('security.password_hasher');

        $loader = new Loader();
        $loader->addFixture(new AppFixtures($hasher));
        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
