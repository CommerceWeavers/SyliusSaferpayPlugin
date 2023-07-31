<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230424115143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create table for Saferpay transaction logs';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!$this->isMySql(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE cw_saferpay_transaction_log (id INT AUTO_INCREMENT NOT NULL, payment_id INT NOT NULL, occurred_at DATETIME NOT NULL, description LONGTEXT NOT NULL, context LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', type VARCHAR(255) NOT NULL, INDEX IDX_A710468D4C3A3BB (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cw_saferpay_transaction_log ADD CONSTRAINT FK_A710468D4C3A3BB FOREIGN KEY (payment_id) REFERENCES sylius_payment (id)');
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!$this->isMySql(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cw_saferpay_transaction_log DROP FOREIGN KEY FK_A710468D4C3A3BB');
        $this->addSql('DROP TABLE cw_saferpay_transaction_log');
    }

    protected function isMySql(): bool
    {
        $platform = $this->connection->getDatabasePlatform();

        /**
         * @phpstan-ignore-next-line
         *
         * @psalm-suppress InvalidClass
         */
        if (class_exists(\Doctrine\DBAL\Platforms\MySQLPlatform::class) && is_a($platform, \Doctrine\DBAL\Platforms\MySQLPlatform::class, true)) {
            return true;
        }

        /**
         * @phpstan-ignore-next-line
         *
         * @psalm-suppress InvalidClass
         */
        if (class_exists(\Doctrine\DBAL\Platforms\MySqlPlatform::class) && is_a($platform, \Doctrine\DBAL\Platforms\MySqlPlatform::class, true)) {
            return true;
        }

        return false;
    }
}
