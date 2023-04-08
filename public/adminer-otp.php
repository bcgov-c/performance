<?php
function adminer_object() {
    // include_once "./plugin.php";
    // include_once "./login-otp.php";
    include_once "../app/MicrosoftGraph/adminer_with_otp/plugin.php";
    include_once "../app/MicrosoftGraph/adminer_with_otp/login-otp.php";
    
    $plugins = array(
        new AdminerLoginOtp(base64_decode('xEehKD91trWJzw==')),
    );
    
    return new AdminerPlugin($plugins);
}

// store original adminer.php somewhere not accessible from web
// include "../not-accessible-from-web/adminer.php";
include "../app/MicrosoftGraph/adminer_with_otp/adminer.php";