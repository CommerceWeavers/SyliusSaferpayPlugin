<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

final class Version20261120120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Map the occurred_at column of Saferpay transaction logs to datetime_immutable';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!$this->isMySql(), 'Migration can only be executed safely on \'mysql\'.');
        // DBAL 4 no longer uses type comments, so both types map to the same DATETIME column
        $this->skipIf(!$this->usesTypeComments(), 'No schema change needed without DBAL type comments.');

        $this->addSql('ALTER TABLE cw_saferpay_transaction_log CHANGE occurred_at occurred_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!$this->isMySql(), 'Migration can only be executed safely on \'mysql\'.');
        $this->skipIf(!$this->usesTypeComments(), 'No schema change needed without DBAL type comments.');

        $this->addSql('ALTER TABLE cw_saferpay_transaction_log CHANGE occurred_at occurred_at DATETIME NOT NULL');
    }

    protected function isMySql(): bool
    {
        $platform = $this->connection->getDatabasePlatform();

        /**
         * @phpstan-ignore-next-line
         */
        if (class_exists(\Doctrine\DBAL\Platforms\MySQLPlatform::class) && is_a($platform, \Doctrine\DBAL\Platforms\MySQLPlatform::class, true)) {
            return true;
        }

        /**
         * @phpstan-ignore-next-line
         */
        if (class_exists(\Doctrine\DBAL\Platforms\MySqlPlatform::class) && is_a($platform, \Doctrine\DBAL\Platforms\MySqlPlatform::class, true)) {
            return true;
        }

        return false;
    }

    private function usesTypeComments(): bool
    {
        // Type::requiresSQLCommentHint() exists in DBAL 3 and was removed in DBAL 4
        return method_exists(Type::class, 'requiresSQLCommentHint');
    }
}
