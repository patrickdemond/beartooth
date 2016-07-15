-- Patch to upgrade database to version 1.2.0

SET AUTOCOMMIT=0;

SOURCE queue.sql
SOURCE queue_has_participant.sql
SOURCE operation.sql
SOURCE role_has_operation.sql
SOURCE queue_restriction.sql;

SOURCE update_version_number.sql

COMMIT;