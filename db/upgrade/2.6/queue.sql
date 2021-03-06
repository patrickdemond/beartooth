-- proceedure used by patch_queue
DROP PROCEDURE IF EXISTS set_queue_id;
DELIMITER //
CREATE PROCEDURE set_queue_id( old_id INT, new_id INT )
  BEGIN
    UPDATE queue SET id = new_id WHERE id = old_id;
    UPDATE queue SET parent_queue_id = new_id WHERE parent_queue_id = old_id;
    UPDATE assignment SET queue_id = new_id WHERE queue_id = old_id;
  END //
DELIMITER ;

DROP PROCEDURE IF EXISTS patch_queue;
DELIMITER //
CREATE PROCEDURE patch_queue()
  BEGIN
    SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
    SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

    SELECT "Adding collection disabled queue" AS "";

    SET @test = ( SELECT COUNT(*) FROM queue WHERE name = "collection disabled" );
    IF @test = 0 THEN
      -- increment all queue ids by 1 from the quota disabled queue onward
      SET @id = ( SELECT MAX( id ) FROM queue );
      SET @min_id = ( SELECT id FROM queue WHERE name = "quota disabled" );
      WHILE @id >= @min_id DO
        CALL set_queue_id( @id, @id + 1 );
        SET @id = @id - 1;
      END WHILE;

      SELECT id INTO @parent_queue_id FROM queue WHERE name = "qnaire";

      INSERT INTO queue SET
        id = @min_id,
        name = "collection disabled",
        title = "Participants belonging to a disabled collection",
        rank = NULL,
        time_specific = 0,
        parent_queue_id = @parent_queue_id,
        description = "Participants who belong to a collection which has been marked as disabled for the current questionnaire.";
    END IF;

    SELECT "Adding not participating queue" AS "";

    SET @test = ( SELECT COUNT(*) FROM queue WHERE name = "not participating" );
    IF @test = 0 THEN
      -- increment all queue ids by 1 from the final hold queue onward
      SET @id = ( SELECT MAX( id ) FROM queue );
      SET @min_id = ( SELECT id FROM queue WHERE name = "final hold" );
      WHILE @id >= @min_id DO
        CALL set_queue_id( @id, @id + 1 );
        SET @id = @id - 1;
      END WHILE;

      SELECT id INTO @parent_queue_id FROM queue WHERE name = "ineligible";

      INSERT INTO queue SET
        id = @min_id,
        name = "not participating",
        title = "Participants who do not wish to participate",
        rank = NULL,
        time_specific = 0,
        parent_queue_id = @parent_queue_id,
        description = "Participants who do not wish to participate in the study.";
    END IF;

    SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
    SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
  END //
DELIMITER ;

-- now call the procedure and remove the procedure
CALL patch_queue();
DROP PROCEDURE IF EXISTS patch_queue;
DROP PROCEDURE IF EXISTS set_queue_id;

SELECT "Renaming the quota disabled queue to stratum disabled" AS "";

UPDATE queue
SET name = "stratum disabled",
    title = "Participant's stratum is disabled",
    description = "Participants who belong to a stratum which has been disabled for the active questionnaire"
WHERE name = "quota disabled";
