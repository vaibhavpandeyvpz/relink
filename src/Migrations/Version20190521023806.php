<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

final class Version20190521023806 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('settings');
        $table->addColumn('id', Type::INTEGER, [
            'autoincrement' => true,
            'unsigned' => true,
        ]);
        $table->addColumn('name', Type::STRING, ['length' => 180]);
        $table->addColumn('value', Type::TEXT, ['notnull' => false]);
        $table->addColumn('created_at', Type::DATETIME, ['notnull' => false]);
        $table->addColumn('updated_at', Type::DATETIME, ['notnull' => false]);
        $table->addUniqueIndex(['name']);
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('settings');
    }
}
