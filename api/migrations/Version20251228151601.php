<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251228151601 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_following (user_source INT NOT NULL, user_target INT NOT NULL, PRIMARY KEY(user_source, user_target))');
        $this->addSql('CREATE INDEX IDX_715F00073AD8644E ON user_following (user_source)');
        $this->addSql('CREATE INDEX IDX_715F0007233D34C1 ON user_following (user_target)');
        $this->addSql('ALTER TABLE user_following ADD CONSTRAINT FK_715F00073AD8644E FOREIGN KEY (user_source) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_following ADD CONSTRAINT FK_715F0007233D34C1 FOREIGN KEY (user_target) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD street VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD house_number VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD city VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD zip_code VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD country VARCHAR(2) DEFAULT \'DE\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE user_following DROP CONSTRAINT FK_715F00073AD8644E');
        $this->addSql('ALTER TABLE user_following DROP CONSTRAINT FK_715F0007233D34C1');
        $this->addSql('DROP TABLE user_following');
        $this->addSql('ALTER TABLE "user" DROP street');
        $this->addSql('ALTER TABLE "user" DROP house_number');
        $this->addSql('ALTER TABLE "user" DROP city');
        $this->addSql('ALTER TABLE "user" DROP zip_code');
        $this->addSql('ALTER TABLE "user" DROP country');
    }
}
