<?php
$idperiph = getArg('idperiph');
$operation = getArg('operation',false,NULL);
$print = getArg('print',false,false);
    
//-- Reload auto each day
$CACHE_DURATION = 24*60; // minutes
$last_reload = loadVariable('last_reload');
if ((time() - $last_reload) / 60 > $CACHE_DURATION)
{
    $operation = "reload";
}
//--
    
switch ( $operation ) {
        case "reload" :
            $conso_devices = array();
            $list = getPeriphList();
            foreach ($list as $device){
                if ($device['utilisation_id'] == 26 && $device['controller_module_id'] != $idperiph) {
                    $conso_devices[]= $device;
                }
            }
            saveVariable('conso_devices', $conso_devices);
            saveVariable('last_reload', time());
        
        default:
            $total = 0;
            $print_devices = array("total" => 0, "devices" => array());
            $conso_devices = loadVariable('conso_devices');
        
            foreach ($conso_devices as $device){
                

                $value = getValue($device["controller_module_id"]);
                
                if ($value["value"] > 0 && $device["parent_controller_module_id"]!= "") {
                    $parent_value = getValue($device["parent_controller_module_id"]);
                    
                    if ($parent_value["value"] == 0) {
                        $value = getValue($device["controller_module_id"]);
                        if ($print) $print_devices["devices"][$device["controller_module_id"]] = array("name" => $device["full_name"], "value" => "0 (".$value["value"].")");
                    } else {
                        $total += $value["value"];
                        if ($print) $print_devices["devices"][$device["controller_module_id"]] = array("name" => $device["full_name"], "value" => $value["value"]);
                    }
                } else {
                    $total += $value["value"];
                    if ($print) $print_devices["devices"][$device["controller_module_id"]] = array("name" => $device["full_name"], "value" => $value["value"]);
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
        print "	<conso id='".$id."'>".PHP_EOL;
        print "		<name>".addslashes(utf8_encode($device["name"]))."</name>".PHP_EOL;
        print "		<value>".$device["value"]."</value>".PHP_EOL;
        print "	</conso>".PHP_EOL;

    }
    print " </consos>".PHP_EOL;
    print " <conso_totale>".$print_devices["total"]."</conso_totale>".PHP_EOL;
    print "</eedomus_consos>";
}

?>