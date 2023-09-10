<?php

// --------------------------------------
// 環境情報
$onion_slice_env = (object) array(
    "url" => $_SERVER['ONITON_SLICE_URL'] ?? null,
    "realpath_data_dir" => $_SERVER['ONITON_SLICE_DATA_DIR'] ?? realpath(__DIR__).'/'.preg_replace('/\.[a-zA-Z0-9]*$/', '', basename(__FILE__)).'_files/',
    "git_remote" => $_SERVER['ONITON_SLICE_GIT_REMOTE'] ?? null,
    "api_token" => $_SERVER['ONITON_SLICE_API_TOKEN'] ?? null,
    "project_id" => $_SERVER['ONITON_SLICE_PROJECT_ID'] ?? null,
);

clearstatcache();

if( !is_dir($onion_slice_env->realpath_data_dir) ){
    trigger_error('Data directory is not exists.');
    exit();
}
if( !is_writable($onion_slice_env->realpath_data_dir) ){
    trigger_error('Data directory is not writable.');
    exit();
}

if( !is_dir($onion_slice_env->realpath_data_dir.'/standby/') ){
    mkdir($onion_slice_env->realpath_data_dir.'/standby/');
}


// --------------------------------------
// 配信スケジュールを取得する
$schedule_json = file_get_contents(
    $onion_slice_env->url.'?api=proj.'.urlencode($onion_slice_env->project_id).'.get_schedule',
    false,
    stream_context_create(array(
        'http' => array(
            'method'=> 'GET',
            'header'=> implode("\r\n", array(
                'Content-Type: application/x-www-form-urlencoded',
                'X-API-KEY: '.$onion_slice_env->api_token,
            )),
        ),
    )));
$schedule = json_decode($schedule_json);

foreach($schedule->schedule as $schedule_id => $schedule_info){
    $realpath_basedir = $onion_slice_env->realpath_data_dir.'/standby/'.urlencode($schedule_id).'/';
    if(!is_dir($realpath_basedir)){
        mkdir($realpath_basedir);
    }

    $cd = realpath('.');
    chdir($realpath_basedir);

    shell_exec('git clone  --depth 1 '.$onion_slice_env->git_remote.'');
    // sleep(1);
    // shell_exec('git checkout -f '.$schedule_info->revision.'');
    // shell_exec('git reset --hard');

    chdir($cd);
}


exit();
