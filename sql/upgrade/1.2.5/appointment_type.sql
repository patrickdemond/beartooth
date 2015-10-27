SELECT "Creating new appointment_type table" AS "";

CREATE TABLE IF NOT EXISTS appointment_type (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  update_timestamp TIMESTAMP NOT NULL,
  create_timestamp TIMESTAMP NOT NULL,
  name VARCHAR(45) NOT NULL,
  qnaire_id INT UNSIGNED NOT NULL,
  description TEXT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX uq_name (name ASC),
  INDEX fk_qnaire_id (qnaire_id ASC),
  CONSTRAINT fk_appointment_type_qnaire_id
    FOREIGN KEY (qnaire_id)
    REFERENCES qnaire (id)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
