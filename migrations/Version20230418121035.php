<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractMigration;

final class Version20230418121035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create cw_saferpay_transaction_log table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cw_saferpay_transaction_log (id INT AUTO_INCREMENT NOT NULL, state VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, context LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE cw_saferpay_transaction_log');
    }
}
