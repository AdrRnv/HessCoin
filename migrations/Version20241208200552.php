<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241208200552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE purchase (id INT AUTO_INCREMENT NOT NULL, buyer_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_6117D13B6C755722 (buyer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE purchase_product (id INT AUTO_INCREMENT NOT NULL, purchase_id INT NOT NULL, product_id INT NOT NULL, price DOUBLE PRECISION NOT NULL, INDEX IDX_C890CED4558FBEB9 (purchase_id), INDEX IDX_C890CED44584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B6C755722 FOREIGN KEY (buyer_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE purchase_product ADD CONSTRAINT FK_C890CED4558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchase (id)');
        $this->addSql('ALTER TABLE purchase_product ADD CONSTRAINT FK_C890CED44584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B6C755722');
        $this->addSql('ALTER TABLE purchase_product DROP FOREIGN KEY FK_C890CED4558FBEB9');
        $this->addSql('ALTER TABLE purchase_product DROP FOREIGN KEY FK_C890CED44584665A');
        $this->addSql('DROP TABLE purchase');
        $this->addSql('DROP TABLE purchase_product');
    }
}
