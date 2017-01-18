<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants for Warpwire Activity Module
 *
 * @package    mod_warpwire
 * @copyright  2016 Warpwire <https://warpwire.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* Moodle core API */

/**
 * Returns the information on whether the Warpwire Activity Module
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function warpwire_supports($feature) {

    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the Warpwire Activity Module into the database
 *
 * @param stdClass $warpwire Submitted data from the form in mod_form.php
 * @param mod_warpwire_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted warpwire record
 */
function warpwire_add_instance(stdClass $warpwire, mod_warpwire_mod_form $mform = null) {
    global $DB;

    $warpwire->timecreated = time();

    // You may have to add extra stuff in here.

    $warpwire->id = $DB->insert_record('warpwire', $warpwire);

    warpwire_grade_item_update($warpwire);

    return $warpwire->id;
}

/**
 * Updates an instance of the Warpwire Activity Module in the database
 *
 * @param stdClass $warpwire An object from the form in mod_form.php
 * @param mod_warpwire_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function warpwire_update_instance(stdClass $warpwire, mod_warpwire_mod_form $mform = null) {
    global $DB;

    $warpwire->timemodified = time();
    $warpwire->id = $warpwire->instance;

    // You may have to add extra stuff in here.

    $result = $DB->update_record('warpwire', $warpwire);

    warpwire_grade_item_update($warpwire);

    return $result;
}

/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every Warpwire Activity Module event in the site is checked, else
 * only Warpwire Activity Module events belonging to the course specified are checked.
 * This is only required if the module is generating calendar events.
 *
 * @param int $courseid Course ID
 * @return bool
 */
function warpwire_refresh_events($courseid = 0) {
    global $DB;

    if ($courseid == 0) {
        if (!$warpwires = $DB->get_records('warpwire')) {
            return true;
        }
    } else {
        if (!$warpwires = $DB->get_records('warpwire', array('course' => $courseid))) {
            return true;
        }
    }

    return true;
}

/**
 * Removes an instance of the Warpwire Activity Module from the database
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function warpwire_delete_instance($id) {
    global $DB;

    if (! $warpwire = $DB->get_record('warpwire', array('id' => $id))) {
        return false;
    }

    // Delete any dependent records here.

    $DB->delete_records('warpwire', array('id' => $warpwire->id));

    warpwire_grade_item_delete($warpwire);

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of the Warpwire Activity Module
 * Used for user activity reports.
 *
 * @param stdClass $course The course record
 * @param stdClass $user The user record
 * @param cm_info|stdClass $mod The course module info object or record
 * @param stdClass $warpwire The warpwire instance record
 * @return stdClass|null
 */
function warpwire_user_outline($course, $user, $mod, $warpwire) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $warpwire the module instance record
 */
function warpwire_user_complete($course, $user, $mod, $warpwire) { }

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in Warpwire Activity Module activities and print it out.
 *
 * @param stdClass $course The course record
 * @param bool $viewfullnames Should we display full names
 * @param int $timestart Print activity since this timestamp
 * @return boolean True if anything was printed, otherwise false
 */
function warpwire_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Prepares the recent activity data
 *
 * @param array $activities sequentially indexed array of objects with added 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 */
function warpwire_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) { }

/**
 * Prints single activity item prepared by {@link warpwire_get_recent_mod_activity()}
 *
 * @param stdClass $activity activity record with added 'cmid' property
 * @param int $courseid the id of the course we produce the report for
 * @param bool $detail print detailed report
 * @param array $modnames as returned by {@link get_module_types_names()}
 * @param bool $viewfullnames display users' full names
 */
function warpwire_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) { }

/**
 * Function to be run periodically according to the moodle cron
 *
 * Note that this has been deprecated in favour of scheduled task API.
 *
 * @return boolean
 */
function warpwire_cron () {
    return true;
}

/**
 * Returns all other capabilitiess used in the module
 *
 * @return array
 */
function warpwire_get_extra_capabilities() {
    return array();
}

/* Gradebook API */

/**
 * Is a given scale used by the instance of warpwire?
 *
 * This function returns if a scale is being used by one Warpwire Activity Module
 * if it has support for grading and scales.
 *
 * @param int $warpwireid ID of an instance of this module
 * @param int $scaleid ID of the scale
 * @return bool true if the scale is used by the given warpwire instance
 */
function warpwire_scale_used($warpwireid, $scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('warpwire', array('id' => $warpwireid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of the Warpwire Activity Module.
 *
 * @param int $scaleid ID of the scale
 * @return boolean true if the scale is used by any warpwire instance
 */
function warpwire_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('warpwire', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the given Warpwire Activity Module instance
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $warpwire instance object with extra cmidnumber and modname property
 * @param bool $reset reset grades in the gradebook
 * @return void
 */
function warpwire_grade_item_update(stdClass $warpwire, $reset=false) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($warpwire->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($warpwire->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $warpwire->grade;
        $item['grademin']  = 0;
    } else if ($warpwire->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$warpwire->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('mod/warpwire', $warpwire->course, 'mod', 'warpwire',
            $warpwire->id, 0, null, $item);
}

/**
 * Delete grade item for given Warpwire Activity Module instance
 *
 * @param stdClass $warpwire instance object
 * @return grade_item
 */
function warpwire_grade_item_delete($warpwire) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/warpwire', $warpwire->course, 'mod', 'warpwire',
            $warpwire->id, 0, null, array('deleted' => 1));
}

/**
 * Update Warpwire Activity Module grades in the gradebook
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $warpwire instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 */
function warpwire_update_grades(stdClass $warpwire, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();

    grade_update('mod/warpwire', $warpwire->course, 'mod', 'warpwire', $warpwire->id, 0, $grades);
}

/* File API */

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function warpwire_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for Warpwire Activity Module file areas
 *
 * @package mod_warpwire
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function warpwire_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the Warpwire Activity Module file areas
 *
 * @package mod_warpwire
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the warpwire's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function warpwire_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}
