<?php
/**
 * error_codes.inc.php
 * 
 * This file is where all error codes are defined.
 * All error code are named after the class and function they occur in.
 */

/**
 * Error number category defines.
 */
define( 'ARGUMENT_BEARTOOTH_BASE_ERRNO',   160000 );
define( 'DATABASE_BEARTOOTH_BASE_ERRNO',   260000 );
define( 'LDAP_BEARTOOTH_BASE_ERRNO',       360000 );
define( 'NOTICE_BEARTOOTH_BASE_ERRNO',     460000 );
define( 'PERMISSION_BEARTOOTH_BASE_ERRNO', 560000 );
define( 'RUNTIME_BEARTOOTH_BASE_ERRNO',    660000 );
define( 'SYSTEM_BEARTOOTH_BASE_ERRNO',     760000 );
define( 'TEMPLATE_BEARTOOTH_BASE_ERRNO',   860000 );
define( 'VOIP_BEARTOOTH_BASE_ERRNO',       960000 );

/**
 * "argument" error codes
 */
define( 'ARGUMENT__BEARTOOTH_BUSINESS_SETTING_MANAGER____CONSTRUCT__ERRNO',
        ARGUMENT_BEARTOOTH_BASE_ERRNO + 1 );
define( 'ARGUMENT__BEARTOOTH_BUSINESS_VOIP_CALL____CONSTRUCT__ERRNO',
        ARGUMENT_BEARTOOTH_BASE_ERRNO + 2 );
define( 'ARGUMENT__BEARTOOTH_BUSINESS_VOIP_MANAGER__CALL__ERRNO',
        ARGUMENT_BEARTOOTH_BASE_ERRNO + 3 );
define( 'ARGUMENT__BEARTOOTH_DATABASE_QUEUE__GET_QUERY_PARTS__ERRNO',
        ARGUMENT_BEARTOOTH_BASE_ERRNO + 4 );
define( 'ARGUMENT__BEARTOOTH_UI_PULL_PARTICIPANT_LIST__VALIDATE__ERRNO',
        ARGUMENT_BEARTOOTH_BASE_ERRNO + 5 );
define( 'ARGUMENT__BEARTOOTH_UI_PULL_PARTICIPANT_LIST__SETUP__ERRNO',
        ARGUMENT_BEARTOOTH_BASE_ERRNO + 6 );
define( 'ARGUMENT__BEARTOOTH_UI_PUSH_ONYX_CONSENT__EXECUTE__ERRNO',
        ARGUMENT_BEARTOOTH_BASE_ERRNO + 7 );
define( 'ARGUMENT__BEARTOOTH_UI_PUSH_ONYX_PARTICIPANTS__EXECUTE__ERRNO',
        ARGUMENT_BEARTOOTH_BASE_ERRNO + 8 );
define( 'ARGUMENT__BEARTOOTH_UI_PUSH_ONYX_PROXY__EXECUTE__ERRNO',
        ARGUMENT_BEARTOOTH_BASE_ERRNO + 9 );

/**
 * "database" error codes
 * 
 * Since database errors already have codes this list is likely to stay empty.
 */

/**
 * "ldap" error codes
 * 
 * Since ldap errors already have codes this list is likely to stay empty.
 */

/**
 * "notice" error codes
 */
define( 'NOTICE__BEARTOOTH_BUSINESS_VOIP_MANAGER__CALL__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 1 );
define( 'NOTICE__BEARTOOTH_DATABASE_APPOINTMENT__SAVE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 2 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_ADDRESS_EDIT__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 3 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_ADDRESS_NEW__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 4 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_APPOINTMENT_EDIT__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 5 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_APPOINTMENT_NEW__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 6 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_ASSIGNMENT_BEGIN__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 7 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_ASSIGNMENT_BEGIN__EXECUTE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 8 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_ASSIGNMENT_END__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 9 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_INTERVIEW_EDIT__EXECUTE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 10 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_ONYX_INSTANCE_DELETE__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 11 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_ONYX_INSTANCE_EDIT__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 12 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_ONYX_INSTANCE_NEW__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 13 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_PARTICIPANT_NEW__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 14 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_PHONE_CALL_BEGIN__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 15 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_PHONE_EDIT__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 16 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_PHONE_NEW__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 17 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_QNAIRE_NEW__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 18 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_QUEUE_RESTRICTION_DELETE__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 19 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_QUEUE_RESTRICTION_EDIT__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 20 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_QUEUE_RESTRICTION_NEW__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 21 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_QUOTA_NEW__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 22 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_SITE_EDIT__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 23 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_SITE_NEW__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 24 );
define( 'NOTICE__BEARTOOTH_UI_PUSH_VOIP_DTMF__VALIDATE__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 25 );
define( 'NOTICE__BEARTOOTH_UI_WIDGET_SELF_ASSIGNMENT__SETUP__ERRNO',
        NOTICE_BEARTOOTH_BASE_ERRNO + 26 );

/**
 * "permission" error codes
 */

/**
 * "runtime" error codes
 */
define( 'RUNTIME__BEARTOOTH_BUSINESS_LDAP_MANAGER__SET_USER_PASSWORD__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 1 );
define( 'RUNTIME__BEARTOOTH_BUSINESS_SETTING_MANAGER____CONSTRUCT__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 2 );
define( 'RUNTIME__BEARTOOTH_BUSINESS_VOIP_MANAGER__INITIALIZE__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 3 );
define( 'RUNTIME__BEARTOOTH_BUSINESS_VOIP_MANAGER__CALL__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 4 );
define( 'RUNTIME__BEARTOOTH_DATABASE_APPOINTMENT__VALIDATE_DATE__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 5 );
define( 'RUNTIME__BEARTOOTH_DATABASE_LIMESURVEY_RECORD____CALL__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 6 );
define( 'RUNTIME__BEARTOOTH_DATABASE_LIMESURVEY_SID_RECORD__GET_TABLE_NAME__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 7 );
define( 'RUNTIME__BEARTOOTH_DATABASE_LIMESURVEY_SURVEY_TIMINGS__GET_TABLE_NAME__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 8 );
define( 'RUNTIME__BEARTOOTH_DATABASE_QUEUE__GET_QUERY_PARTS__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 9 );
define( 'RUNTIME__BEARTOOTH_UI_PULL_APPOINTMENT_LIST__EXECUTE__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 10 );
define( 'RUNTIME__BEARTOOTH_UI_PULL_PARTICIPANT_PRIMARY__PREPARE__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 11 );
define( 'RUNTIME__BEARTOOTH_UI_PUSH_ONYX_CONSENT__EXECUTE__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 12 );
define( 'RUNTIME__BEARTOOTH_UI_PUSH_ONYX_PARTICIPANTS__EXECUTE__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 13 );
define( 'RUNTIME__BEARTOOTH_UI_PUSH_ONYX_PROXY__EXECUTE__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 14 );
define( 'RUNTIME__BEARTOOTH_UI_PUSH_PARTICIPANT_WITHDRAW__EXECUTE__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 15 );
define( 'RUNTIME__BEARTOOTH_UI_PUSH_PHONE_CALL_BEGIN__VALIDATE__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 16 );
define( 'RUNTIME__BEARTOOTH_UI_WIDGET_ADDRESS_ADD__SETUP__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 17 );
define( 'RUNTIME__BEARTOOTH_UI_WIDGET_APPOINTMENT_ADD__SETUP__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 18 );
define( 'RUNTIME__BEARTOOTH_UI_WIDGET_ASSIGNMENT_LIST__DETERMINE_RECORD_COUNT__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 19 );
define( 'RUNTIME__BEARTOOTH_UI_WIDGET_ASSIGNMENT_LIST__DETERMINE_RECORD_LIST__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 20 );
define( 'RUNTIME__BEARTOOTH_UI_WIDGET_AVAILABILITY_ADD__SETUP__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 21 );
define( 'RUNTIME__BEARTOOTH_UI_WIDGET_CONSENT_ADD__SETUP__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 22 );
define( 'RUNTIME__BEARTOOTH_UI_WIDGET_PHASE_ADD__SETUP__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 23 );
define( 'RUNTIME__BEARTOOTH_UI_WIDGET_PHONE_ADD__SETUP__ERRNO',
        RUNTIME_BEARTOOTH_BASE_ERRNO + 24 );

/**
 * "system" error codes
 * 
 * Since system errors already have codes this list is likely to stay empty.
 * Note the following PHP error codes:
 *      1: error,
 *      2: warning,
 *      4: parse,
 *      8: notice,
 *     16: core error,
 *     32: core warning,
 *     64: compile error,
 *    128: compile warning,
 *    256: user error,
 *    512: user warning,
 *   1024: user notice
 */

/**
 * "template" error codes
 * 
 * Since template errors already have codes this list is likely to stay empty.
 */

/**
 * "voip" error codes
 */
define( 'VOIP__BEARTOOTH_BUSINESS_VOIP_CALL__DTMF__ERRNO',
        VOIP_BEARTOOTH_BASE_ERRNO + 1 );
define( 'VOIP__BEARTOOTH_BUSINESS_VOIP_CALL__PLAY_SOUND__ERRNO',
        VOIP_BEARTOOTH_BASE_ERRNO + 2 );
define( 'VOIP__BEARTOOTH_BUSINESS_VOIP_CALL__START_MONITORING__ERRNO',
        VOIP_BEARTOOTH_BASE_ERRNO + 3 );
define( 'VOIP__BEARTOOTH_BUSINESS_VOIP_CALL__STOP_MONITORING__ERRNO',
        VOIP_BEARTOOTH_BASE_ERRNO + 4 );
define( 'VOIP__BEARTOOTH_BUSINESS_VOIP_MANAGER__INITIALIZE__ERRNO',
        VOIP_BEARTOOTH_BASE_ERRNO + 5 );
define( 'VOIP__BEARTOOTH_BUSINESS_VOIP_MANAGER__REBUILD_CALL_LIST__ERRNO',
        VOIP_BEARTOOTH_BASE_ERRNO + 6 );
define( 'VOIP__BEARTOOTH_BUSINESS_VOIP_MANAGER__CALL__ERRNO',
        VOIP_BEARTOOTH_BASE_ERRNO + 7 );

