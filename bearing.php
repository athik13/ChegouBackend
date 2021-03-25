<?php
function bearing($lat1, $long1, $lat2, $long2){
    $bearingradians = atan2(asin($long1-$long2)*cos($lat2),
    cos($lat1)*sin($lat2) - sin($lat1)*cos($lat2)*cos($long1-$long2)); 
    $bearingdegrees = abs(rad2deg($bearingradians));
    return $bearingdegrees;
}

function bearing1( $lat1_d, $lon1_d, $lat2_d, $lon2_d ){

   $lat1 = deg2rad($lat1_d);
   $lon1 = deg2rad($lon1_d);
   $lat2 = deg2rad($lat2_d);
   $lon2 = deg2rad($lon2_d);

   $L    = $lon2 - $lon1;

   $cosD = sin($lat1)*sin($lat2) + cos($lat1)*cos($lat2)*cos($L);
   $D    = acos($cosD);
   $cosC = (sin($lat2) - $cosD*sin($lat1))/(sin($D)*cos($lat1));
    
    $C = 180.0*acos($cosC)/pi();

    if( sin($L) < 0.0 )
        $C = 360.0 - $C;

    return $C;
}

echo bearing1(23.013826, 72.503887, 23.026741, 72.507664);
?>