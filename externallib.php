<?php
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

class local_certificate_api_external extends external_api {

    // Define the parameters for the web service function.
    public static function get_certificate_url_parameters() {
        return new external_function_parameters(
            array(
                'userid' => new external_value(PARAM_INT, 'ID of the user', VALUE_DEFAULT, 0), // User ID is optional and defaults to 0.
                'useremail' => new external_value(PARAM_TEXT, 'Email of the user', VALUE_OPTIONAL) // User email is optional.
            )
        );
    }

    // The main function that checks the certificate and returns the URLs.
    public static function get_certificate_url($userid = 0, $useremail = null) {
        global $DB;

        // Validate the parameters.
        $params = self::validate_parameters(self::get_certificate_url_parameters(), array('userid' => $userid, 'useremail' => $useremail));

        // Check if both userid and useremail are provided, and throw an error if that's the case.
        if (!empty($params['userid']) && !empty($params['useremail'])) {
            throw new invalid_parameter_exception('Both userid and useremail cannot be provided at the same time. Please provide only one.');
        }

        // Ensure at least one parameter is provided: either userid or useremail.
        if ($params['userid'] == 0 && empty($params['useremail'])) {
            throw new invalid_parameter_exception('Either userid or useremail must be provided.');
        }

        // Determine the user based on the provided parameters.
        if ($params['userid'] != 0) {
            // If userid is provided (not 0), we use it to find the user.
            $user = $DB->get_record('user', array('id' => $params['userid']), 'id, lastaccess');
        } elseif (!empty($params['useremail'])) {
            // If useremail is provided, we use it to find the user.
            $user = $DB->get_record('user', array('email' => $params['useremail']), 'id, lastaccess');
        }

        // Check if the user exists.
        if (!$user) {
            throw new invalid_parameter_exception('Invalid user ID or email.');
        }

        // Look for all certificate issues for the user.
        $certificates = $DB->get_records('tool_certificate_issues', array('userid' => $user->id));

        if (!$certificates) {
            // If no certificates were found, return an error message.
            return array('status' => 'error', 'message' => 'The user has not completed any courses yet.');
        }

        $lastaccess = $user->lastaccess;

        // Initialize an array to store the generated URLs.
        $urls = array();

        // Loop through all the certificates and generate URLs.
        foreach ($certificates as $certificate) {
            $code = $certificate->code;

            // Generate the URL for each certificate PDF.
            $url = new moodle_url('/pluginfile.php/1/tool_certificate/issues/' . $lastaccess . '/' . $code . '.pdf');

            // Add the URL to the list of URLs.
            $urls[] = $url->out(false);
        }

        // Return structured array (which will be automatically converted to JSON by Moodle's web service).
        return array(
            'status' => 'success',
            'certificate_urls' => $urls // Return the array of URLs.
        );
    }

    // Define the return type of the web service function.
    public static function get_certificate_url_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, 'Status of the request'),
                'certificate_urls' => new external_multiple_structure( // Use external_multiple_structure for multiple URLs.
                    new external_value(PARAM_URL, 'The URL of a certificate')
                ),
            )
        );
    }
}