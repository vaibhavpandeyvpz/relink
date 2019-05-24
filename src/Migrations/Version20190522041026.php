<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

final class Version20190522041026 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->getTable('links');
        $table->addColumn('meta_title', Type::STRING, ['notnull' => false]);
        $table->addColumn('meta_description', Type::STRING, ['notnull' => false]);
    }

    public function down(Schema $schema) : void
    {
        $table = $schema->getTable('links');
        $table->dropColumn('meta_title');
        $table->dropColumn('meta_description');
    }
}
