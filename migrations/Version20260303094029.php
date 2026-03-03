<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303094029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE booster (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, price INT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE booster_card (booster_id INT NOT NULL, card_id INT NOT NULL, INDEX IDX_AF3FB9DCF85E4930 (booster_id), INDEX IDX_AF3FB9DC4ACC9A20 (card_id), PRIMARY KEY (booster_id, card_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE card (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, rarity VARCHAR(50) NOT NULL, attack INT NOT NULL, defense INT NOT NULL, hp INT NOT NULL, energy_cost INT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE card_effect (id INT AUTO_INCREMENT NOT NULL, effect_type VARCHAR(50) NOT NULL, value INT NOT NULL, condition_type VARCHAR(50) DEFAULT NULL, condition_value VARCHAR(50) DEFAULT NULL, card_id INT NOT NULL, INDEX IDX_C45FC2864ACC9A20 (card_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE collectionne (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, usere_id INT NOT NULL, card_id INT NOT NULL, INDEX IDX_57D7FCEA12C1BC7E (usere_id), INDEX IDX_57D7FCEA4ACC9A20 (card_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE deck (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL, usere_id INT NOT NULL, INDEX IDX_4FAC363712C1BC7E (usere_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE deck_card (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, deck_id INT NOT NULL, card_id INT NOT NULL, INDEX IDX_2AF3DCED111948DC (deck_id), INDEX IDX_2AF3DCED4ACC9A20 (card_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE game_match (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(50) NOT NULL, started_at DATETIME DEFAULT NULL, ended_at DATETIME DEFAULT NULL, current_turn INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE match_action (id INT AUTO_INCREMENT NOT NULL, action_type VARCHAR(50) NOT NULL, payload JSON NOT NULL, created_at DATETIME NOT NULL, match_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_E6A375932ABEACD6 (match_id), INDEX IDX_E6A3759399E6F5DF (player_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE match_player (id INT AUTO_INCREMENT NOT NULL, life_points INT NOT NULL, energy INT NOT NULL, match_id INT NOT NULL, usere_id INT NOT NULL, deck_id INT NOT NULL, INDEX IDX_397683642ABEACD6 (match_id), INDEX IDX_3976836412C1BC7E (usere_id), INDEX IDX_39768364111948DC (deck_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE match_turn (id INT AUTO_INCREMENT NOT NULL, turn_number INT NOT NULL, started_at DATETIME NOT NULL, ended_at DATETIME DEFAULT NULL, match_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_414FA4352ABEACD6 (match_id), INDEX IDX_414FA43599E6F5DF (player_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(50) NOT NULL, elo_rating INT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE booster_card ADD CONSTRAINT FK_AF3FB9DCF85E4930 FOREIGN KEY (booster_id) REFERENCES booster (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE booster_card ADD CONSTRAINT FK_AF3FB9DC4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card_effect ADD CONSTRAINT FK_C45FC2864ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE collectionne ADD CONSTRAINT FK_57D7FCEA12C1BC7E FOREIGN KEY (usere_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE collectionne ADD CONSTRAINT FK_57D7FCEA4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC363712C1BC7E FOREIGN KEY (usere_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE deck_card ADD CONSTRAINT FK_2AF3DCED111948DC FOREIGN KEY (deck_id) REFERENCES deck (id)');
        $this->addSql('ALTER TABLE deck_card ADD CONSTRAINT FK_2AF3DCED4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE match_action ADD CONSTRAINT FK_E6A375932ABEACD6 FOREIGN KEY (match_id) REFERENCES game_match (id)');
        $this->addSql('ALTER TABLE match_action ADD CONSTRAINT FK_E6A3759399E6F5DF FOREIGN KEY (player_id) REFERENCES match_player (id)');
        $this->addSql('ALTER TABLE match_player ADD CONSTRAINT FK_397683642ABEACD6 FOREIGN KEY (match_id) REFERENCES game_match (id)');
        $this->addSql('ALTER TABLE match_player ADD CONSTRAINT FK_3976836412C1BC7E FOREIGN KEY (usere_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE match_player ADD CONSTRAINT FK_39768364111948DC FOREIGN KEY (deck_id) REFERENCES deck (id)');
        $this->addSql('ALTER TABLE match_turn ADD CONSTRAINT FK_414FA4352ABEACD6 FOREIGN KEY (match_id) REFERENCES game_match (id)');
        $this->addSql('ALTER TABLE match_turn ADD CONSTRAINT FK_414FA43599E6F5DF FOREIGN KEY (player_id) REFERENCES match_player (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booster_card DROP FOREIGN KEY FK_AF3FB9DCF85E4930');
        $this->addSql('ALTER TABLE booster_card DROP FOREIGN KEY FK_AF3FB9DC4ACC9A20');
        $this->addSql('ALTER TABLE card_effect DROP FOREIGN KEY FK_C45FC2864ACC9A20');
        $this->addSql('ALTER TABLE collectionne DROP FOREIGN KEY FK_57D7FCEA12C1BC7E');
        $this->addSql('ALTER TABLE collectionne DROP FOREIGN KEY FK_57D7FCEA4ACC9A20');
        $this->addSql('ALTER TABLE deck DROP FOREIGN KEY FK_4FAC363712C1BC7E');
        $this->addSql('ALTER TABLE deck_card DROP FOREIGN KEY FK_2AF3DCED111948DC');
        $this->addSql('ALTER TABLE deck_card DROP FOREIGN KEY FK_2AF3DCED4ACC9A20');
        $this->addSql('ALTER TABLE match_action DROP FOREIGN KEY FK_E6A375932ABEACD6');
        $this->addSql('ALTER TABLE match_action DROP FOREIGN KEY FK_E6A3759399E6F5DF');
        $this->addSql('ALTER TABLE match_player DROP FOREIGN KEY FK_397683642ABEACD6');
        $this->addSql('ALTER TABLE match_player DROP FOREIGN KEY FK_3976836412C1BC7E');
        $this->addSql('ALTER TABLE match_player DROP FOREIGN KEY FK_39768364111948DC');
        $this->addSql('ALTER TABLE match_turn DROP FOREIGN KEY FK_414FA4352ABEACD6');
        $this->addSql('ALTER TABLE match_turn DROP FOREIGN KEY FK_414FA43599E6F5DF');
        $this->addSql('DROP TABLE booster');
        $this->addSql('DROP TABLE booster_card');
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE card_effect');
        $this->addSql('DROP TABLE collectionne');
        $this->addSql('DROP TABLE deck');
        $this->addSql('DROP TABLE deck_card');
        $this->addSql('DROP TABLE game_match');
        $this->addSql('DROP TABLE match_action');
        $this->addSql('DROP TABLE match_player');
        $this->addSql('DROP TABLE match_turn');
        $this->addSql('DROP TABLE `user`');
    }
}
