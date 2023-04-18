<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230424062633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create cw_saferpay_transaction_log table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cw_saferpay_transaction_log (id INT AUTO_INCREMENT NOT NULL, payment_id INT NOT NULL, created_at DATETIME NOT NULL, status VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, context LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_A710468D4C3A3BB (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cw_saferpay_transaction_log ADD CONSTRAINT FK_A710468D4C3A3BB FOREIGN KEY (payment_id) REFERENCES sylius_payment (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cw_saferpay_transaction_log DROP FOREIGN KEY FK_A710468D4C3A3BB');
        $this->addSql('DROP TABLE cw_saferpay_transaction_log');
    }
}
