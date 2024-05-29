<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240529092415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE annonce DROP FOREIGN KEY FK_F65593E5573038B9');
        $this->addSql('DROP INDEX IDX_F65593E5573038B9 ON annonce');
        $this->addSql('ALTER TABLE annonce CHANGE utilisateur_id publier_id INT NOT NULL');
        $this->addSql('ALTER TABLE annonce ADD CONSTRAINT FK_F65593E5573038B9 FOREIGN KEY (publier_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_F65593E5573038B9 ON annonce (publier_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE annonce DROP FOREIGN KEY FK_F65593E5573038B9');
        $this->addSql('DROP INDEX IDX_F65593E5573038B9 ON annonce');
        $this->addSql('ALTER TABLE annonce CHANGE publier_id utilisateur_id INT NOT NULL');
        $this->addSql('ALTER TABLE annonce ADD CONSTRAINT FK_F65593E5573038B9 FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_F65593E5573038B9 ON annonce (utilisateur_id)');
    }
}
