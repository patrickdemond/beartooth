DROP PROCEDURE IF EXISTS patch_appointment;
DELIMITER //
CREATE PROCEDURE patch_appointment()
  BEGIN

    SELECT "Adding new appointment_type_id column to appointment table" AS "";

    SET @test = (
      SELECT COUNT(*)
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = "appointment"
      AND COLUMN_NAME = "appointment_type_id" );
    IF @test = 0 THEN
      ALTER TABLE appointment
      ADD COLUMN appointment_type_id INT UNSIGNED NULL DEFAULT NULL
      AFTER address_id;

      ALTER TABLE appointment
      ADD INDEX fk_appointment_type_id ( appointment_type_id ASC );

      ALTER TABLE appointment
      ADD CONSTRAINT fk_appointment_appointment_type_id
      FOREIGN KEY (appointment_type_id)
      REFERENCES appointment_type (id)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION;

    END IF;

  END //
DELIMITER ;

CALL patch_appointment();
DROP PROCEDURE IF EXISTS patch_appointment;
