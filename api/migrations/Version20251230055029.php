<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251230055029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // 1. Add new columns (nullable first for data migration)
        $this->addSql('ALTER TABLE "user" ADD managed_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649873649CA FOREIGN KEY (managed_by_id) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8D93D649873649CA ON "user" (managed_by_id)');

        $this->addSql('ALTER TABLE animal ADD user_account_id INT DEFAULT NULL'); // Initially NULL

        // 2. DATA MIGRATION: Convert Animals to Users
        // Create a User record for each Animal
        // Using a dummy password (bcrypt hash for 'password')
        $this->addSql('
            INSERT INTO "user" (id, email, roles, password, first_name, is_banned, created_at, account_type, country, managed_by_id)
            SELECT
                nextval(\'user_id_seq\'),
                concat(\'pet_\', id, \'@pawsocial.internal\'),
                \'["ROLE_PET"]\',
                \'$2y$13$7s7.1.1.1.1.1.1.1.1.1.\', -- Dummy hash
                name,
                false,
                NOW(),
                \'pet\',
                \'DE\',
                owner_id
            FROM animal
        ');

        // Link Animal back to the newly created User
        $this->addSql('
            UPDATE animal
            SET user_account_id = u.id
            FROM "user" u
            WHERE u.email = concat(\'pet_\', animal.id, \'@pawsocial.internal\')
        ');

        // 3. Enforce constraints and cleanup
        $this->addSql('ALTER TABLE animal ALTER COLUMN user_account_id SET NOT NULL');
        $this->addSql('ALTER TABLE animal ADD CONSTRAINT FK_6AAB231F3C0C9956 FOREIGN KEY (user_account_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6AAB231F3C0C9956 ON animal (user_account_id)');

        // Remove old owner relation
        $this->addSql('ALTER TABLE animal DROP CONSTRAINT fk_6aab231f7e3c61f9');
        $this->addSql('DROP INDEX idx_6aab231f7e3c61f9');
        $this->addSql('ALTER TABLE animal DROP owner_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649873649CA');
        $this->addSql('DROP INDEX IDX_8D93D649873649CA');
        $this->addSql('ALTER TABLE "user" DROP managed_by_id');
        $this->addSql('ALTER TABLE animal DROP CONSTRAINT FK_6AAB231F3C0C9956');
        $this->addSql('DROP INDEX UNIQ_6AAB231F3C0C9956');
        $this->addSql('ALTER TABLE animal ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE animal DROP user_account_id');
        $this->addSql('ALTER TABLE animal ADD CONSTRAINT fk_6aab231f7e3c61f9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_6aab231f7e3c61f9 ON animal (owner_id)');
    }
}
