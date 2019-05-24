<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

final class Version20190520011153 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('users');
        $table->addColumn('id', Type::INTEGER, [
            'autoincrement' => true,
            'unsigned' => true,
        ]);
        $table->addColumn('name', Type::STRING, ['length' => 255]);
        $table->addColumn('email', Type::STRING, ['length' => 180]);
        $table->addColumn('roles', Type::TEXT);
        $table->addColumn('password', Type::STRING, ['length' => 255]);
        $table->addColumn('created_at', Type::DATETIME, ['notnull' => false]);
        $table->addColumn('updated_at', Type::DATETIME, ['notnull' => false]);
        $table->addUniqueIndex(['email']);
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('users');
    }
}
