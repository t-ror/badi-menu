<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260307202514 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_household (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(32) NOT NULL, image VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_FCAC70F55E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE app_household_meal (id INT AUTO_INCREMENT NOT NULL, household_id INT NOT NULL, meal_id INT NOT NULL, INDEX IDX_40538B83E79FF843 (household_id), INDEX IDX_40538B83639666D6 (meal_id), UNIQUE INDEX UNIQ_40538B83E79FF843639666D6 (household_id, meal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE app_ingredient (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(32) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE app_meal (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, method LONGTEXT DEFAULT NULL, created_by_user_id INT NOT NULL, INDEX IDX_9BD8AB3C7D182D95 (created_by_user_id), UNIQUE INDEX UNIQ_9BD8AB3CF47645AE (url), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE meal_meal_tag (meal_id INT NOT NULL, meal_tag_id INT NOT NULL, INDEX IDX_639C25E5639666D6 (meal_id), INDEX IDX_639C25E5D5D8DE18 (meal_tag_id), PRIMARY KEY(meal_id, meal_tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE app_meal_ingredient (id INT AUTO_INCREMENT NOT NULL, amount VARCHAR(32) DEFAULT NULL, meal_id INT NOT NULL, ingredient_id INT NOT NULL, INDEX IDX_A619EB73639666D6 (meal_id), INDEX IDX_A619EB73933FE08C (ingredient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE app_meal_tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(32) NOT NULL, household_id INT NOT NULL, INDEX IDX_F0E5E3AE79FF843 (household_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE app_user (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(32) NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(64) NOT NULL, roles JSON NOT NULL, verified TINYINT(1) DEFAULT 0 NOT NULL, token VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_88BDF3E95E237E06 (name), UNIQUE INDEX UNIQ_88BDF3E9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE app_user_household (id INT AUTO_INCREMENT NOT NULL, date_joined DATETIME NOT NULL, date_last_selected DATETIME DEFAULT NULL, allowed TINYINT(1) DEFAULT 0 NOT NULL, allowed_to_cook TINYINT(1) DEFAULT 0 NOT NULL, user_id INT NOT NULL, household_id INT NOT NULL, INDEX IDX_8BD930DAA76ED395 (user_id), INDEX IDX_8BD930DAE79FF843 (household_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE app_user_meal (id INT AUTO_INCREMENT NOT NULL, able_to_prepare TINYINT(1) DEFAULT 0 NOT NULL, favorite TINYINT(1) DEFAULT 0 NOT NULL, user_id INT NOT NULL, meal_id INT NOT NULL, INDEX IDX_4C189D60A76ED395 (user_id), INDEX IDX_4C189D60639666D6 (meal_id), UNIQUE INDEX UNIQ_4C189D60A76ED395639666D6 (user_id, meal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE app_household_meal ADD CONSTRAINT FK_40538B83E79FF843 FOREIGN KEY (household_id) REFERENCES app_household (id)');
        $this->addSql('ALTER TABLE app_household_meal ADD CONSTRAINT FK_40538B83639666D6 FOREIGN KEY (meal_id) REFERENCES app_meal (id)');
        $this->addSql('ALTER TABLE app_meal ADD CONSTRAINT FK_9BD8AB3C7D182D95 FOREIGN KEY (created_by_user_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE meal_meal_tag ADD CONSTRAINT FK_639C25E5639666D6 FOREIGN KEY (meal_id) REFERENCES app_meal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meal_meal_tag ADD CONSTRAINT FK_639C25E5D5D8DE18 FOREIGN KEY (meal_tag_id) REFERENCES app_meal_tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_meal_ingredient ADD CONSTRAINT FK_A619EB73639666D6 FOREIGN KEY (meal_id) REFERENCES app_meal (id)');
        $this->addSql('ALTER TABLE app_meal_ingredient ADD CONSTRAINT FK_A619EB73933FE08C FOREIGN KEY (ingredient_id) REFERENCES app_ingredient (id)');
        $this->addSql('ALTER TABLE app_meal_tag ADD CONSTRAINT FK_F0E5E3AE79FF843 FOREIGN KEY (household_id) REFERENCES app_household (id)');
        $this->addSql('ALTER TABLE app_user_household ADD CONSTRAINT FK_8BD930DAA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE app_user_household ADD CONSTRAINT FK_8BD930DAE79FF843 FOREIGN KEY (household_id) REFERENCES app_household (id)');
        $this->addSql('ALTER TABLE app_user_meal ADD CONSTRAINT FK_4C189D60A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE app_user_meal ADD CONSTRAINT FK_4C189D60639666D6 FOREIGN KEY (meal_id) REFERENCES app_meal (id)');
    }
}
