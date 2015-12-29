<?php
$idperiph = getArg('idperiph');
$operation = getArg('operation',false,NULL);
    
    echo $operation;
    
switch ( $operation ) {
        case "reload" :
            $conso_devices = array();
        
            $api_user = getArg('api_user');
            $api_secret  = getArg('api_secret');
        
            $url = "http://localhost/api/get?action=periph.list";
            $url .= "&api_user=$api_user";
            $url .= "&api_secret=$api_secret";
        
            $result = httpQuery($url);
            $json = sdk_json_decode($result);
            $list = $json['body'];
            foreach ($list as $device){
                if ($device['usage_id'] == 26 && $device['periph_id'] != $idperiph ) {
                    $conso_devices[]= $device['periph_id'];
                }
            }
            saveVariable('conso_devices', $conso_devices);
        
        default:
            $total = 0;
            $conso_devices = loadVariable('conso_devices');
        
            foreach ($conso_devices as $deviceid){
                $value = getValue($deviceid);
                $total += $value["value"];
            }
            setValue($idperiph,$total);
        break;
}
?>
