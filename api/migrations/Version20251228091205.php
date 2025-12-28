<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251228091205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin_message (id SERIAL NOT NULL, sender_id INT NOT NULL, related_post_id INT DEFAULT NULL, message TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_read BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7281B2F6F624B39D ON admin_message (sender_id)');
        $this->addSql('CREATE INDEX IDX_7281B2F67490C989 ON admin_message (related_post_id)');
        $this->addSql('COMMENT ON COLUMN admin_message.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE admin_message ADD CONSTRAINT FK_7281B2F6F624B39D FOREIGN KEY (sender_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE admin_message ADD CONSTRAINT FK_7281B2F67490C989 FOREIGN KEY (related_post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE admin_message DROP CONSTRAINT FK_7281B2F6F624B39D');
        $this->addSql('ALTER TABLE admin_message DROP CONSTRAINT FK_7281B2F67490C989');
        $this->addSql('DROP TABLE admin_message');
    }
}
