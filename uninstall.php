<?php
defined('WP_UNINSTALL_PLUGIN') || die();

delete_option('ww_yubico_otp');
delete_metadata('user', 0, '_ww_yotp', null, true);
