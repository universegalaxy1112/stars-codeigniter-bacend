<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| JWT Config
| -------------------------------------------------------------------------
| Values to be used in Jwt Client library
|
*/

$config['jwt_issuer'] = 'Dick Arnold';

// must be non-empty
$config['jwt_secret_key'] = 'n2dhXLaI6UBpIRSAHdIL';

// expiry time since a JWT is issued (in seconds); set NULL to never expired
$config['jwt_expiry'] = 86400;