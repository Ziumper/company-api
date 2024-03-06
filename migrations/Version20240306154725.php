<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240306154725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company ALTER zipcode TYPE VARCHAR(6)');
        $this->addSql('CREATE UNIQUE INDEX tax_reference_number ON company (tax_reference_number)');
        $this->addSql('CREATE UNIQUE INDEX zipcode ON company (zipcode)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX tax_reference_number');
        $this->addSql('DROP INDEX zipcode');
        $this->addSql('ALTER TABLE company ALTER zipcode TYPE VARCHAR(255)');
    }
}
