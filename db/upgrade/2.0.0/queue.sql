-- proceedure used by patch_queue
DROP PROCEDURE IF EXISTS set_queue_id;
DELIMITER //
CREATE PROCEDURE set_queue_id( old_id INT, new_id INT )
  BEGIN
    UPDATE queue SET id = new_id WHERE id = old_id;
    UPDATE queue SET parent_queue_id = new_id WHERE parent_queue_id = old_id;
    UPDATE assignment SET queue_id = new_id WHERE queue_id = old_id;
    UPDATE queue_state SET queue_id = new_id WHERE queue_id = old_id;
  END //
DELIMITER ;

DROP PROCEDURE IF EXISTS patch_queue;
DELIMITER //
CREATE PROCEDURE patch_queue()
  BEGIN
    SELECT "Adding no address queue" AS "";

    SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
    SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

    SET @test = ( SELECT COUNT(*) FROM queue WHERE name = "no address" );
    IF @test = 0 THEN
      -- increment all queue ids by 1 from the eligible queue onward
      SET @id = ( SELECT MAX( id ) FROM queue );
      SET @min_id = ( SELECT id FROM queue WHERE name = "eligible" );
      WHILE @id >= @min_id DO
        CALL set_queue_id( @id, @id + 1 );
        SET @id = @id - 1;
      END WHILE;

      SET @parent_queue_id = ( SELECT id FROM queue WHERE name = "ineligible" );

      -- add the new no active address queue
      INSERT INTO queue SET
        id = @min_id,
        name = "no address",
        title = "Participants with no address",
        rank = NULL,
        qnaire_specific = 0,
        time_specific = 0,
        parent_queue_id = @parent_queue_id,
        description = "Participants who are not eligible because they do not have an address.";
    END IF;

    SELECT "Adding no active address queue" AS "";

    SET @test = ( SELECT COUNT(*) FROM queue WHERE name = "no active address" );
    IF @test = 0 THEN
      -- increment all queue ids by 1 from the quota disabled queue onward
      SET @id = ( SELECT MAX( id ) FROM queue );
      SET @min_id = ( SELECT id FROM queue WHERE name = "quota disabled" );
      WHILE @id >= @min_id DO
        CALL set_queue_id( @id, @id + 1 );
        SET @id = @id - 1;
      END WHILE;

      SET @parent_queue_id = ( SELECT id FROM queue WHERE name = "qnaire" );

      -- add the new no active address queue
      INSERT INTO queue SET
        id = @min_id,
        name = "no active address",
        title = "Participants with no active address",
        rank = NULL,
        qnaire_specific = 0,
        time_specific = 0,
        parent_queue_id = @parent_queue_id,
        description = "Participants who are unreachable since they currently have no active address.";
    END IF;

    SELECT "Adding no site queue" AS "";

    SET @test = ( SELECT COUNT(*) FROM queue WHERE name = "no site" );
    IF @test = 0 THEN
      -- increment all queue ids by 1 from the quota disabled queue onward
      SET @id = ( SELECT MAX( id ) FROM queue );
      SET @min_id = ( SELECT id FROM queue WHERE name = "quota disabled" );
      WHILE @id >= @min_id DO
        CALL set_queue_id( @id, @id + 1 );
        SET @id = @id - 1;
      END WHILE;

      SET @parent_queue_id = ( SELECT id FROM queue WHERE name = "qnaire" );

      -- add the new no active address queue
      INSERT INTO queue SET
        id = @min_id,
        name = "no site",
        title = "Participants who have no site",
        rank = NULL,
        qnaire_specific = 0,
        time_specific = 0,
        parent_queue_id = @parent_queue_id,
        description = "Participants who will not be assigned since they do not belong to any site.";
    END IF;

    SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
    SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
  END //
DELIMITER ;

-- now call the procedure and remove the procedure
CALL patch_queue();
DROP PROCEDURE IF EXISTS patch_queue;
DROP PROCEDURE IF EXISTS set_queue_id;

SELECT "Removing extraneous whitespace in queue descriptions" AS "";

UPDATE queue SET description = REPLACE( description, "\n      ", " " );

SELECT "Converting more queues to time-specific mode" AS "";

UPDATE queue SET time_specific = 1 WHERE id >= ( SELECT id FROM ( 
  SELECT id FROM queue WHERE name = "outside calling time"
) AS t );
