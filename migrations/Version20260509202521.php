<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260509202521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ADD nom VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD prenom VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD gsm VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD adresse VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD ville VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD code_postal VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD actif BOOLEAN NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" DROP nom');
        $this->addSql('ALTER TABLE "user" DROP prenom');
        $this->addSql('ALTER TABLE "user" DROP gsm');
        $this->addSql('ALTER TABLE "user" DROP adresse');
        $this->addSql('ALTER TABLE "user" DROP ville');
        $this->addSql('ALTER TABLE "user" DROP code_postal');
        $this->addSql('ALTER TABLE "user" DROP actif');
    }
}
