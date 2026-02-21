<?php

namespace App\Traits;

use \proxycheck\proxycheck;

trait ProxycheckTrait
{
    public function proxycheck($ip)
    {
        //verify that $ip is a valid IP address
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        
        $proxycheck_options = array(
            'API_KEY' => env('PROXYCHECK_API_KEY'),
            'DAY_RESTRCTOR' => 7,
            'VPN_DETECTION' => 3,
            'RISK_DATA' => 1,
        );
        
        $result_array = \proxycheck\proxycheck::check($ip, $proxycheck_options);
        
        //check if status is ok
        if(env('APP_ENV') == 'production') {
            if ($result_array['status'] != 'ok') {
                throw new \Exception('Proxy detection error: ' . $result_array['status']);
            }
            
            if ($result_array[$ip]['detections']['proxy'] == true 
            || $result_array[$ip]['detections']['vpn'] == true
            || $result_array[$ip]['detections']['tor'] == true
            || $result_array[$ip]['detections']['anonymous'] == true
            ) {
                return true;
            } else {
                return false;
            }
        } else {return false;}
    }
}