DROP PROCEDURE IF EXISTS patch_role_has_service;
DELIMITER //
CREATE PROCEDURE patch_role_has_service()
  BEGIN

    -- determine the @cenozo database name
    SET @cenozo = (
      SELECT unique_constraint_schema
      FROM information_schema.referential_constraints
      WHERE constraint_schema = DATABASE()
      AND constraint_name = "fk_access_site_id" );

    SELECT "Creating new role_has_service table" AS "";

    SET @test = (
      SELECT COUNT(*)
      FROM information_schema.TABLES
      WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = "role_has_service" );
    IF @test = 0 THEN
      -- add new role_has_service_table
      SET @sql = CONCAT(
        "CREATE TABLE IF NOT EXISTS role_has_service ( ",
          "role_id INT UNSIGNED NOT NULL, ",
          "service_id INT UNSIGNED NOT NULL, ",
          "update_timestamp TIMESTAMP NOT NULL, ",
          "create_timestamp TIMESTAMP NOT NULL, ",
          "PRIMARY KEY (role_id, service_id), ",
          "INDEX fk_role_id (role_id ASC), ",
          "INDEX fk_service_id (service_id ASC), ",
          "CONSTRAINT fk_role_has_service_service_id ",
            "FOREIGN KEY (service_id) ",
            "REFERENCES service (id) ",
            "ON DELETE CASCADE ",
            "ON UPDATE NO ACTION, ",
          "CONSTRAINT fk_role_has_service_role_id ",
            "FOREIGN KEY (role_id) ",
            "REFERENCES ", @cenozo, ".role (id) ",
            "ON DELETE CASCADE ",
            "ON UPDATE NO ACTION) ",
        "ENGINE = InnoDB" );
      PREPARE statement FROM @sql;
      EXECUTE statement;
      DEALLOCATE PREPARE statement;
    END IF;

    -- populate table
    DELETE FROM role_has_service;

    -- administrator
    SET @sql = CONCAT(
      "INSERT INTO role_has_service( role_id, service_id ) ",
      "SELECT role.id, service.id ",
      "FROM ", @cenozo, ".role, service ",
      "WHERE role.name = 'administrator' ",
      "AND service.restricted = 1 ",
      "AND service.id NOT IN ( ",
        "SELECT id FROM service ",
        "WHERE subject = 'appointment' ",
        "OR subject = 'callback' ",
        "OR ( subject = 'assignment' AND method = 'POST' ) ",
        "OR ( subject = 'phone_call' AND method != 'DELETE' ) ",
        "OR subject IN( 'token' ) ",
      ")" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

    -- curator
    SET @sql = CONCAT(
      "INSERT INTO role_has_service( role_id, service_id ) ",
      "SELECT role.id, service.id ",
      "FROM ", @cenozo, ".role, service ",
      "WHERE role.name = 'curator' ",
      "AND service.restricted = 1 ",
      "AND service.subject IN ( ",
        "'address', 'alternate', 'consent', 'event', 'language', 'note', ",
        "'participant', 'phone', 'jurisdiction', 'source', 'state' ",
      ")" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

    -- helpline and interviewer
    SET @sql = CONCAT(
      "INSERT INTO role_has_service( role_id, service_id ) ",
      "SELECT role.id, service.id ",
      "FROM ", @cenozo, ".role, service ",
      "WHERE role.name IN( 'helpline', 'interviewer' ) ",
      "AND service.restricted = 1 ",
      "AND service.subject IN ( 'appointment', 'assignment', 'callback', 'participant', 'phone_call', 'token' )" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

    -- onyx
    -- no access to restricted services required

    -- coordinator
    SET @sql = CONCAT(
      "INSERT INTO role_has_service( role_id, service_id ) ",
      "SELECT role.id, service.id ",
      "FROM ", @cenozo, ".role, service ",
      "WHERE role.name = 'coordinator' ",
      "AND service.restricted = 1 ",
      "AND service.id NOT IN ( ",
        "SELECT id FROM service ",
        "WHERE subject IN( ",
          "'address', 'alternate', 'application', 'appointment_type', 'collection', 'consent', 'event', ",
          "'interview', 'jurisdiction', 'language', 'onyx_instance', 'phase', 'phone', 'qnaire', 'quota', ",
          "'jurisdiction', 'recording', 'recording_list', 'script', 'source', 'state' ) ",
        "OR ( subject = 'setting' AND method = 'GET' ) ",
        "OR ( subject = 'site' AND method IN ( 'DELETE', 'POST' ) ) ",
      ")" );
    PREPARE statement FROM @sql;
    EXECUTE statement;
    DEALLOCATE PREPARE statement;

  END //
DELIMITER ;

CALL patch_role_has_service();
DROP PROCEDURE IF EXISTS patch_role_has_service;