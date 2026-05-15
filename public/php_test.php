<?php
if (function_exists('gd_info')) {
    $gdInfo = gd_info();
    if (isset($gdInfo['WebP Support']) && $gdInfo['WebP Support']) {
        echo "WebP is supported by GD.";
    } else {
        echo "WebP is NOT supported by GD.";
    }
} else {
    echo "GD library is not installed.";
}
phpinfo(); // This will show all PHP info, including the full GD section
?>