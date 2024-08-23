<?php
class DistanceCalculateClass{
	function calculateDistance($origin, $destinations){
		$api_key = "AIzaSyDkCjzv4fVu7wlsp31Tu0AnpbyQaxm4Kz8";
		$url='https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins='.$origin.'&destinations='.$destinations.'&key='.$api_key;
		$json_data = file_get_contents($url);	
		$distance = $this->fnlGetDistance($json_data);
		$roundOfDistance = round($distance);
		if($roundOfDistance < $distance){
			$roundOfDistance++;
		}
		return $roundOfDistance;
	}

	function fnlGetDistance($json_data)
	{
		$json_a=json_decode($json_data,true);
		$total_distance=0;
		foreach($json_a as $key => $value) 
		{
			if($key=="rows")
			{
				foreach($value as $key1 => $value1) 
				{
					foreach($value1 as $key2 => $value2) 
					{
						foreach($value2 as $key3 => $value3) 
						{
							foreach($value3 as $key4 => $value4) 
							{
								if($key4=="distance")
								{
									foreach($value4 as $key5 => $value5) 
									{
										if($key5=="text")
										{
											// $total_distance=$total_distance + str_replace(" km","",$value5);
											$dist = $value5;
											// echo $dist;
											if(strpos($dist, 'km') !== false){
												// echo $dist;
												$dist1 = str_replace(" km","",$dist);
												// echo $dist1.'--';
												$dist = $dist1*1000;
											}
											else{
												$dist1 = str_replace(" m","",$dist);
												// echo $dist1.'--';
												$dist = $dist1;
											}
											$total_distance = ($total_distance + $dist)/1000;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return $total_distance;
	}
}
?>