<?php
$idperiph = getArg('idperiph');
$operation = getArg('operation',false,NULL);
$print = getArg('print',false,false);
    
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
                if ($device['usage_id'] == 26 && $device['periph_id'] != $idperiph) {
                    $conso_devices[]= $device;
                }
            }
            saveVariable('conso_devices', $conso_devices);
        
        default:
            $total = 0;
            $print_devices = array("total" => 0, "devices" => array());
            $conso_devices = loadVariable('conso_devices');
        
            foreach ($conso_devices as $device){
                

                $value = getValue($device["periph_id"]);
                
                if ($value["value"] > 0 && $device["parent_periph_id"]!= "") {
                    $parent_value = getValue($device["parent_periph_id"]);
                    
                    if ($parent_value["value"] == 0) {
                        $value = getValue($device["periph_id"]);
                        if ($print) $print_devices["devices"][$device["periph_id"]] = array("name" => $device["name"], "value" => "0 (".$value["value"].")");
                    } else {
                        $total += $value["value"];
                        if ($print) $print_devices["devices"][$device["periph_id"]] = array("name" => $device["name"], "value" => $value["value"]);
                    }
                } else {
                    $total += $value["value"];
                    if ($print) $print_devices["devices"][$device["periph_id"]] = array("name" => $device["name"], "value" => $value["value"]);
                }
                
            }
            setValue($idperiph,$total);
            if ($print) $print_devices["total"] = $total;
            if ($print) sdk_write_conso($print_devices);
        
        break;
}

function sdk_write_conso($print_devices) {
    sdk_header ("text/xml");
    print "<eedomus_consos>".PHP_EOL;
    print " <consos>".PHP_EOL;
    foreach ($print_devices["devices"] as $id => $device) {
        print "	<conso id=".$id.">".PHP_EOL;
        print "		<name>".addslashes(utf8_encode($device["name"]))."</name>".PHP_EOL;
        print "		<value>".$device["value"]."</value>".PHP_EOL;
        print "	</conso>".PHP_EOL;

    }
    print " </consos>".PHP_EOL;
    print " <conso_totale>".$print_devices["total"]."</conso_totale>".PHP_EOL;
    print "</eedomus_consos>";
}

?>