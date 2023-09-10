<?php

// --------------------------------------
// 環境情報
$onion_slice_env = (object) array(
    "url" => $_SERVER['ONITON_SLICE_URL'] ?? null,
    "api_token" => $_SERVER['ONITON_SLICE_API_TOKEN'] ?? null,
    "project_id" => $_SERVER['ONITON_SLICE_PROJECT_ID'] ?? null,
);


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

var_dump($schedule_json);

exit();
