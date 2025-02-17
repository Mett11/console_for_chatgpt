<?php
header('Content-Type: application/json');
// YOU TOKEN 
$botToken = '7944123584:AAFz-N6nuulgO5IP_lj3WZNbH2UnKZlAuC8';
// body
$data = json_decode(file_get_contents('php://input'), TRUE);
if($data){
    $data_check_string = $data[0];
    $hash = $data[1];
    // create secret kay
    $secret_key = hash_hmac('sha256', $botToken, "WebAppData", TRUE);
    // create hash our (data + secret key)
    $check_hash = hash_hmac('sha256', $data_check_string, $secret_key);
    // check hashes
    if(hash_equals($check_hash, $hash)){
        echo json_encode(['success' => TRUE]); // user is good
    }else{
        echo json_encode(['success' => FALSE]); // user is bad
    }
}
?>