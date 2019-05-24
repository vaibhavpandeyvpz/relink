<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

final class Version20190523025215 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->getTable('links');
        $table->addColumn('user_id', Type::INTEGER, ['unsigned' => true]);
        $table->addForeignKeyConstraint(
            'users',
            ['user_id'],
            ['id'],
            ['onDelete' => 'CASCADE'],
            'links_user_id_foreign'
        );
    }

    public function down(Schema $schema) : void
    {
        $table = $schema->getTable('links');
        $table->removeForeignKey('links_user_id_foreign');
        $table->dropColumn('user_id');
    }
}
