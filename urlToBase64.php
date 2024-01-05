<?php
    $url = "https://www.trinityapplab.in/MealsFly/files/Oct-2023-16/1697451369_Image.jpg";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);

    // echo $output;
    curl_close($ch);
?>

<img src="data:image/jpg;base64,<?php echo base64_encode($output);?>">