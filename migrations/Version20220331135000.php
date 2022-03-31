<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220331135000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add track cache';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cache_item (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', stored_data LONGBLOB NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE track ADD cache_data_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE track ADD CONSTRAINT FK_D6E3F8A634D73F0B FOREIGN KEY (cache_data_id) REFERENCES cache_item (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D6E3F8A634D73F0B ON track (cache_data_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE track DROP FOREIGN KEY FK_D6E3F8A634D73F0B');
        $this->addSql('DROP TABLE cache_item');
        $this->addSql('ALTER TABLE messenger_messages CHANGE body body LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE headers headers LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE queue_name queue_name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE tag CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DROP INDEX UNIQ_D6E3F8A634D73F0B ON track');
        $this->addSql('ALTER TABLE track DROP cache_data_id, CHANGE spotify_id spotify_id VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user CHANGE name name VARCHAR(180) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE access_token access_token VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
