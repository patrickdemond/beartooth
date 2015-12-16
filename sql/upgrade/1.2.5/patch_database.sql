-- Patch to upgrade database to version 1.2.5

SET AUTOCOMMIT=0;

SOURCE activity.sql
SOURCE operation.sql
SOURCE role_has_operation.sql
SOURCE operation2.sql
SOURCE queue_state.sql
SOURCE setting_value.sql
SOURCE setting.sql
SOURCE data_collection.sql
SOURCE appointment_type.sql
SOURCE appointment.sql
SOURCE update_version_number.sql

SELECT "NOTE: Make sure to run limesurvey.php" AS "";

COMMIT;
