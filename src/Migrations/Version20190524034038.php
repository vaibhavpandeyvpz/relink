<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

final class Version20190524034038 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('password_reset_tokens');
        $table->addColumn('id', Type::INTEGER, [
            'autoincrement' => true,
            'unsigned' => true,
        ]);
        $table->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
        $table->addColumn('token', Type::STRING, ['length' => 32]);
        $table->addColumn('expires_at', Type::DATETIME, ['notnull' => false]);
        $table->addColumn('created_at', Type::DATETIME, ['notnull' => false]);
        $table->addColumn('updated_at', Type::DATETIME, ['notnull' => false]);
        $table->addForeignKeyConstraint('users', ['user_id'], ['id'], ['onDelete' => 'CASCADE']);
        $table->addUniqueIndex(['token']);
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('password_reset_tokens');
    }
}
