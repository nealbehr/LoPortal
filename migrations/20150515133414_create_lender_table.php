<?php

use Phinx\Migration\AbstractMigration;

class CreateLenderTable extends AbstractMigration
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
CREATE TABLE `lender` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `address` varchar(255) NOT NULL,
    `disclosure` text NOT NULL,
    `picture` varchar(255),
    `created_at` datetime default NULL,
    `updated_at` datetime default NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `lender` (`id`, `name`, `address`, `disclosure`, `picture`, `created_at`, `updated_at`)
VALUES
	(1, 'Hana Small Business Lending, Inc.', '1000 Wilshire Blvd # 20, Los Angeles, CA 90017', 'Not all applicants will qualify. Some products offered by ABC Lending include modified documentation requirements and may have a higher interest rate, more points or more fees than other products requiring documentation. Minimum FICO, reserve, and other requirements apply. Contact your Loan Officer for additional program guidelines, restrictions, and eligibility requirements. Rates, points, APR’s and programs are subject to change at any time until locked-in. NMLS #2900437', NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
	(2, 'Colorado Lending Source, Ltd.', '1441 18th St, Denver, CO 80202', 'Not all applicants will qualify. Some products offered by ABC Lending include modified documentation requirements and may have a higher interest rate, more points or more fees than other products requiring documentation. Minimum FICO, reserve, and other requirements apply. Contact your Loan Officer for additional program guidelines, restrictions, and eligibility requirements. Rates, points, APR’s and programs are subject to change at any time until locked-in. NMLS #2900437', NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
	(3, 'USA Lending, LLC', '1777 Reisterstown Rd, Suite 345, Baltimore, MD 21208', 'Not all applicants will qualify. Some products offered by ABC Lending include modified documentation requirements and may have a higher interest rate, more points or more fees than other products requiring documentation. Minimum FICO, reserve, and other requirements apply. Contact your Loan Officer for additional program guidelines, restrictions, and eligibility requirements. Rates, points, APR’s and programs are subject to change at any time until locked-in. NMLS #2900437', NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00');

EOL
        );
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('queue');
    }
}