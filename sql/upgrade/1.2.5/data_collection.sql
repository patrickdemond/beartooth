DROP PROCEDURE IF EXISTS patch_data_collection;
DELIMITER //
CREATE PROCEDURE patch_data_collection()
  BEGIN

    SELECT "Adding new take_urine column to data_collection table" AS "";

    SET @test = (
      SELECT COUNT(*)
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = "data_collection"
      AND COLUMN_NAME = "take_urine" );
    IF @test = 0 THEN
      ALTER TABLE data_collection
      ADD COLUMN take_urine TINYINT(1) NULL DEFAULT NULL
      AFTER draw_blood;
    END IF;

  END //
DELIMITER ;

CALL patch_data_collection();
DROP PROCEDURE IF EXISTS patch_data_collection;
