<?php

function map_data($api_data){
    $records = [];
    foreach($api_data as $data){
        $record["title"] = $data["title"];
        $record["channelName"] = $data["channelName"];
        $record["viewCountText"] = $data["viewCountText"];
        $record["lengthText"] = $data["lengthText"];
        $record["publishedTimeText"] = $data["publishedTimeText"];
        array_push($records, $record);
    }
    return $records;
}