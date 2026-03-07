<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260307205935 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // Create a default admin user with a household for local dev env
        if (($_ENV['APP_ENV'] ?? 'prod') === 'dev') {
            // password = abc123
            $this->addSql("INSERT INTO app_user (id, name, password, email, roles, verified, token, image) VALUES (1, 'Admin', '\$2y\$12\$57V.omH9QPtI.vQV7iGRO.hVdcyriJ.bH5m60RRRGyozzbtcw.6GS', 'admin@admin.com', '[\"ROLE_USER_VERIFIED\"]', 1, NULL, NULL)");
            $this->addSql("INSERT INTO app.app_household (id, name, image) VALUES (1, 'Test', null);");
        }
    }
}
