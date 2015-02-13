
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- restocking_alert
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `restocking_alert`;

CREATE TABLE `restocking_alert`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `product_sale_elements_id` INTEGER NOT NULL,
    `email` VARCHAR(255),
    `locale` VARCHAR(45),
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `FI_restocking_alert_product_sale_elements_id` (`product_sale_elements_id`),
    CONSTRAINT `fk_restocking_alert_product_sale_elements_id`
        FOREIGN KEY (`product_sale_elements_id`)
        REFERENCES `product_sale_elements` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
