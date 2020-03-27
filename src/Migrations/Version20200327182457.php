<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200327182457 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE product_alerts (id INT AUTO_INCREMENT NOT NULL, store_id INT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL, last_check DATETIME DEFAULT NULL, stock_out TINYINT(1) DEFAULT \'0\' NOT NULL, product_id INT NOT NULL, product_name VARCHAR(255) DEFAULT NULL, on_break TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_950681CFB092A811 (store_id), INDEX IDX_950681CFA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_alerts ADD CONSTRAINT FK_950681CFB092A811 FOREIGN KEY (store_id) REFERENCES stores (id)');
        $this->addSql('ALTER TABLE product_alerts ADD CONSTRAINT FK_950681CFA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE product_alerts');
    }
}
