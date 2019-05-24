<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

final class Version20190520022252 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('links');
        $table->addColumn('id', Type::INTEGER, [
            'autoincrement' => true,
            'unsigned' => true,
        ]);
        $table->addColumn('slug', Type::STRING, ['length' => 16]);
        $table->addColumn('target', Type::STRING, ['length' => 2083]);
        $table->addColumn('mode', Type::STRING, ['length' => 16]);
        $table->addColumn('expires_at', Type::DATETIME, ['notnull' => false]);
        $table->addColumn('created_at', Type::DATETIME, ['notnull' => false]);
        $table->addColumn('updated_at', Type::DATETIME, ['notnull' => false]);
        $table->addUniqueIndex(['slug']);
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('links');
    }
}
