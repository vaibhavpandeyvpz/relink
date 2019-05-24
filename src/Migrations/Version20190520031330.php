<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

final class Version20190520031330 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('clicks');
        $table->addColumn('id', Type::INTEGER, [
            'autoincrement' => true,
            'unsigned' => true,
        ]);
        $table->addColumn('link_id', Type::INTEGER, ['unsigned' => true]);
        $table->addColumn('browser', Type::STRING, ['notnull' => false]);
        $table->addColumn('browser_name', Type::STRING, ['notnull' => false]);
        $table->addColumn('browser_version', Type::STRING, ['notnull' => false]);
        $table->addColumn('platform', Type::STRING, ['notnull' => false]);
        $table->addColumn('platform_name', Type::STRING, ['notnull' => false]);
        $table->addColumn('platform_version', Type::STRING, ['notnull' => false]);
        $table->addColumn('device', Type::STRING, ['notnull' => false]);
        $table->addColumn('device_brand', Type::STRING, ['notnull' => false]);
        $table->addColumn('device_model', Type::STRING, ['notnull' => false]);
        $table->addColumn('ip_address', Type::STRING, ['length' => 45]);
        $table->addColumn('created_at', Type::DATETIME, ['notnull' => false]);
        $table->addColumn('updated_at', Type::DATETIME, ['notnull' => false]);
        $table->addForeignKeyConstraint('links', ['link_id'], ['id'], ['onDelete' => 'CASCADE']);
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('clicks');
    }
}
