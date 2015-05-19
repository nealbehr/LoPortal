<?php

use Phinx\Migration\AbstractMigration;

class UserLenderLink extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute(<<<EOL

ALTER TABLE `users` ADD  `lender_id` int(11) unsigned NOT NULL;

INSERT IGNORE INTO `lender` (`id`, `name`, `address`, `disclosure`, `picture`, `created_at`, `updated_at`)
VALUES
	(1, 'Hana Small Business Lending, Inc.', '1000 Wilshire Blvd # 20, Los Angeles, CA 90017', 'Not all applicants will qualify. Some products offered by ABC Lending include modified documentation requirements and may have a higher interest rate, more points or more fees than other products requiring documentation. Minimum FICO, reserve, and other requirements apply. Contact your Loan Officer for additional program guidelines, restrictions, and eligibility requirements. Rates, points, APR’s and programs are subject to change at any time until locked-in. NMLS #2900437', '/images/img01.png', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

UPDATE `users` SET `lender_id` = 1;

ALTER TABLE `users` ADD CONSTRAINT fk_lender_id FOREIGN KEY (lender_id) references lender(id);

ALTER TABLE `users` DROP COLUMN `lender`;

EOL
        );
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}