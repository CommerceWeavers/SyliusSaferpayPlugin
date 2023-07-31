<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230506091600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set UUID as a primary key for Saferpay transaction logs';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!$this->isMySql(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cw_saferpay_transaction_log CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!$this->isMySql(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cw_saferpay_transaction_log CHANGE id id INT AUTO_INCREMENT NOT NULL');
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
