SELECT "Adding new operations" AS "";

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "queue_state", "add", true,
"View a form for creating new queue restriction based on site and qnaire." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "push", "queue_state", "delete", true,
"Removes a restriction from a queue based on site and qnaire." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "push", "queue_state", "edit", true,
"Edits a restriction on a queue based on site and qnaire." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "queue_state", "list", true,
"List restrictions on queues based on site and qnaire." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "push", "queue_state", "new", true,
"Add a new restriction to a queue based on site and qnaire." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "queue_state", "view", true,
"View a restriction on a queue based on site and qnaire." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "queue", "add_queue_state", true,
"A form to create a new restrcition to the queue based on site and qnaire." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "push", "queue", "delete_queue_state", true,
"Remove a queue's restriction based on site and qnaire." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "push", "queue", "edit", true,
"Edits a queue's details." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "participant", "proxy", true,
"Pseudo-assignment to handle participant proxyies." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "appointment_type", "add", true,
"View a form for creating a new appointment type." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "push", "appointment_type", "delete", true,
"Removes a appointment type from the system." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "push", "appointment_type", "edit", true,
"Edits a appointment type's details." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "appointment_type", "list", true,
"Lists a questionnaire's appointment types." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "push", "appointment_type", "new", true,
"Creates a new questionnaire appointment type." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "appointment_type", "view", true,
"View the details of a questionnaire's appointment types." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "widget", "qnaire", "add_appointment_type", true,
"View surveys to add as a new appointment_type to a questionnaire." );

INSERT IGNORE INTO operation( type, subject, name, restricted, description )
VALUES( "push", "qnaire", "delete_appointment_type", true,
"Remove appointment_types from a questionnaire." );
