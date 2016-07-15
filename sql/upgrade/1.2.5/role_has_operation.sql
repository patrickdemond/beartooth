DROP PROCEDURE IF EXISTS patch_role_has_operation;
DELIMITER //
CREATE PROCEDURE patch_role_has_operation()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = (
      SELECT unique_constraint_schema
      FROM information_schema.referential_constraints
      WHERE constraint_schema = DATABASE()
      AND constraint_name = "fk_role_has_operation_role_id" );

    SELECT "Adding new operations to roles" AS "";

    SET @sql = CONCAT(
      "INSERT IGNORE INTO role_has_operation( role_id, operation_id ) ",
      "SELECT role.id, operation.id FROM ", @cenozo, ".role, operation ",
      "WHERE subject = 'queue_state' OR operation.name like '%queue_state' ",
      "AND role.name IN ( 'administrator', 'coordinator' )" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

    SET @sql = CONCAT(
      "INSERT IGNORE INTO role_has_operation( role_id, operation_id ) ",
      "SELECT role.id, operation.id FROM ", @cenozo, ".role, operation ",
      "WHERE type = 'push' AND subject = 'queue' AND operation.name = 'edit' ",
      "AND role.name IN( 'administrator', 'coordinator' )" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

    SET @sql = CONCAT(
      "INSERT IGNORE INTO role_has_operation( role_id, operation_id ) ",
      "SELECT role.id, operation.id FROM ", @cenozo, ".role, operation ",
      "WHERE type = 'widget' AND subject = 'participant' AND operation.name = 'hin' ",
      "AND role.name IN( 'administrator', 'coordinator', 'interviewer' )" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

    SET @sql = CONCAT(
      "INSERT IGNORE INTO role_has_operation( role_id, operation_id ) "
      "SELECT role.id, operation.id FROM ", @cenozo, ".role, operation "
      "WHERE type = 'widget' AND subject = 'participant' AND operation.name = 'proxy' "
      "AND operation.restricted = true ",
      "AND role.name IN( 'administrator', 'curator', 'helpline', 'coordinator' )" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

    SET @sql = CONCAT(
      "INSERT IGNORE INTO role_has_operation( role_id, operation_id ) "
      "SELECT role.id, operation.id FROM ", @cenozo, ".role, operation "
      "WHERE subject = 'appointment' AND operation.name = 'report' "
      "AND operation.restricted = true ",
      "AND role.name = 'administrator'" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

    SET @sql = CONCAT(
      "INSERT IGNORE INTO role_has_operation( role_id, operation_id ) "
      "SELECT role.id, operation.id FROM ", @cenozo, ".role, operation "
      "WHERE ( subject = 'appointment_type' OR operation.name LIKE '%appointment_type' ) "
      "AND operation.restricted = true ",
      "AND role.name IN( 'administrator' )" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

    SET @sql = CONCAT(
      "INSERT IGNORE INTO role_has_operation( role_id, operation_id ) "
      "SELECT role.id, operation.id FROM ", @cenozo, ".role, operation "
      "WHERE type = 'push' AND subject = 'onyx' AND operation.name = 'hin' "
      "AND operation.restricted = true ",
      "AND role.name IN( 'onyx' )" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

  END //
DELIMITER ;

CALL patch_role_has_operation();
DROP PROCEDURE IF EXISTS patch_role_has_operation;

SELECT "Removing defunct operations from roles" AS "";

DELETE FROM role_has_operation
WHERE operation_id IN (
  SELECT id FROM operation
  WHERE name IN( "reverse_withdraw", "secondary" )
);