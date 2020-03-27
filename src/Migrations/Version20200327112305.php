<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200327112305 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE stores (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, last_check DATETIME DEFAULT NULL, store VARCHAR(50) NOT NULL, store_id INT NOT NULL, store_name VARCHAR(255) DEFAULT NULL, slot_open TINYINT(1) DEFAULT \'0\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');


        // create existing store

        $this->addSql(<<<SQL
      INSERT INTO stores (store, store_name, store_id, created_at, last_check, slot_open)
  SELECT store, store_name, store_id, created_at, last_check, slot_open
  FROM actions GROUP BY store_name, store_id;
SQL
);


        $this->addSql(<<<SQL
      UPDATE actions
JOIN stores ON actions.store = stores.store AND actions.store_id = stores.store_id
set actions.store_id = stores.id;
SQL
        );


        $this->addSql('ALTER TABLE actions ADD CONSTRAINT FK_548F1EFB092A811 FOREIGN KEY (store_id) REFERENCES stores (id)');



        $this->addSql('ALTER TABLE actions DROP last_check, DROP store, DROP slot_open, DROP store_name');
        $this->addSql('CREATE INDEX IDX_548F1EFB092A811 ON actions (store_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE actions DROP FOREIGN KEY FK_548F1EFB092A811');
        $this->addSql('DROP TABLE stores');
        $this->addSql('DROP INDEX IDX_548F1EFB092A811 ON actions');
        $this->addSql('ALTER TABLE actions ADD last_check DATETIME DEFAULT NULL, ADD store VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD slot_open TINYINT(1) DEFAULT \'0\' NOT NULL, ADD store_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
