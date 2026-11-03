<?php
header('Content-Type: application/json');
echo json_encode([
    'upload_tmp_dir' => ini_get('upload_tmp_dir'),
    'sys_temp_dir' => ini_get('sys_temp_dir'),
    'sys_get_temp_dir' => sys_get_temp_dir(),
]);

