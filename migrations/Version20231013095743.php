<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231013095743 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_recipe_dislike DROP FOREIGN KEY FK_D95A3DB359D8A214');
        $this->addSql('ALTER TABLE user_recipe_dislike DROP FOREIGN KEY FK_D95A3DB3A76ED395');
        $this->addSql('DROP TABLE user_recipe_dislike');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_recipe_dislike (recipe_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_D95A3DB359D8A214 (recipe_id), INDEX IDX_D95A3DB3A76ED395 (user_id), PRIMARY KEY(recipe_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_recipe_dislike ADD CONSTRAINT FK_D95A3DB359D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_recipe_dislike ADD CONSTRAINT FK_D95A3DB3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }
}
