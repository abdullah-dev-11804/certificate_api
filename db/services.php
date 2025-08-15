<?php

$functions = array(
    'local_certificate_api_get_certificate_url' => array(
        'classname'   => 'local_certificate_api_external',
        'methodname'  => 'get_certificate_url',
        'classpath'   => 'local/certificate_api/externallib.php',
        'description' => 'Returns the certificate URL for a given user if the certificate exists.',
        'type'        => 'read', // This is a read-only service.
        'ajax'        => true, // This allows it to be called via AJAX.
    ),
);

$services = array(
    'Certificate API' => array(
        'functions' => array('local_certificate_api_get_certificate_url'),
        'restrictedusers' => 0,
        'enabled' => 1,
    ),
);