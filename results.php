<?php

header('Content-Type: application/json');

$servername = "sci-mysql"; 
$username = "coa123edb"; 
$password = "E4XujVcLcNPhwfBjx-"; 
$dbname = "coa123edb"; 


// Connect to DB
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    echo json_encode(["message" => "Database connection failed."]);
    exit;
}

// error message if invalid server request
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["message" => "Invalid request."]);
    exit;
}

// Fetch input
$cons = $_POST['consultant'] ?? 'none';
$clin = $_POST['clinic'] ?? 'none';
$loca = $_POST['location'] ?? '';
$spec = $_POST['speciality'] ?? 'none';
$date = $_POST['date'] ?? null;

// Check if consultant is available on specified date
function isAvailable($consName, $date, $conn){
    // sql query
  $sql = "SELECT bookings.booking_date AS dates FROM bookings JOIN consultants ON consultants.id = bookings.consultant_id WHERE consultants.name = '$consName';";
  $result = mysqli_query($conn, $sql);

  // check if given date matches one of the booked dates
  $data = [];
  while ($row = $result->fetch_assoc()) {
    if($date === $row['dates']){
      return false;
    }
  }
  return true;
}

// get distance from given location to a clinic
function calcDist($location, $clinic, $conn){
    // api request
  $curl = curl_init("https://geocode.maps.co/search?q=".urlencode($location)."&api_key=6806bc24b1610732931070xvza1686d");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);

    // error message if api request failed
    if (!$response) {
        echo json_encode(["message" => "Failed to fetch location."]);
        exit;
    }

    // error detection
    $data = json_decode($response, true);
    if (empty($data[0]['lat']) || empty($data[0]['lon'])) {
        echo json_encode(["message" => "Invalid location provided."]);
        exit;
    }

    // latitude and longitude of the location user entered
    $userLat = deg2rad($data[0]['lat']);
    $userLon = deg2rad($data[0]['lon']);

    // get long and lat of the clinic
    $sql = "SELECT longitude, latitude FROM `clinics` WHERE name = '$clinic';";
    $result = mysqli_query($conn, $sql);

    // error detection
    if (!$result || $result->num_rows == 0) {
        echo json_encode(["message" => "Clinic not found."]);
        exit;
    }


    $clinicRow = $result->fetch_assoc();
    $clinicLat = deg2rad($clinicRow['latitude']);
    $clinicLon = deg2rad($clinicRow['longitude']);

    // distance equation used to calculate straight line distance between the two locations
    $distance = acos(
        sin($userLat) * sin($clinicLat) +
        cos($userLat) * cos($clinicLat) * cos($clinicLon - $userLon)
    ) * 6371; 

    // return rounded distance
    return round($distance, 2);
}

// ----------- Consultant selected -----------
if ($cons != 'none') {
    $sql = "SELECT consultants.name AS name, consultants.consultation_fee AS cons, clinics.name AS clin, specialities.speciality AS spec, reviews.score as score, reviews.recommend as rec
            FROM consultants
            JOIN clinics ON clinics.id = consultants.clinic_id
            JOIN specialities ON specialities.id = consultants.speciality_id
            JOIN reviews ON reviews.consultant_id = consultants.id
            WHERE consultants.name = '$cons'";

  // if clinic also selected 
    if ($clin != 'none') {
        $sql .= " AND clinics.name = '$clin'";
    }
    $result = mysqli_query($conn, $sql);

    $row = $result->fetch_assoc();

    // Check for clinic mismatch
    if ($clin != 'none' && $row['clin'] != $clin) {
        echo json_encode(["message" => "The selected consultant does not work in the selected clinic"]);
        exit;
    }

    // Check for speciality mismatch
    if ($spec != 'none' && $row['spec'] != $spec) {
        echo json_encode(["message" => "The selected consultant does not have the selected speciality"]);
        exit;
    }

    // if date is also entered, check availablity
    if(!empty($date)){
      $booked = isAvailable($row['name'], $date, $conn);
    }

    // if location is selected, calculate distance
    if(!empty($loca)){
      $distance = calcDist($loca,$row['clin'],$conn);
    }

    // create data array
    $data[] = [
        'name' => $row['name'],
        'spec' => $row['spec'],
        'clinic' => $row['clin'],
        'fee' => $row['cons'],
        'score' => $row['score'],
        'available' => !empty($date) ? $booked : null,
        'distance' => !empty($loca) ? $distance : null
    ];

    // Return consultant details
    echo json_encode($data);
    exit;
}

// ----------- Speciality selected -----------
if ($spec != 'none') {
    $sql = "SELECT consultants.name AS name, consultants.consultation_fee AS fee, clinics.name AS clinic, specialities.speciality AS spec, ROUND(AVG(reviews.score),1) AS score FROM consultants JOIN clinics ON consultants.clinic_id = clinics.id JOIN specialities ON consultants.speciality_id = specialities.id JOIN reviews ON consultants.id= reviews.consultant_id WHERE specialities.speciality = '$spec'";

    // if clinic also selected
    if ($clin != 'none') {
        $sql .= " AND clinics.name = '$clin'";
    }

    // group by the constultants and display in alphabetical order
    $sql .= " GROUP BY consultants.id ORDER BY consultants.name ASC;";

    $result = mysqli_query($conn, $sql);

    // error for speciality and clinic mismatch
    if (!$result || $result->num_rows == 0) {
        echo json_encode(["message" => "No results found for selected speciality/clinic."]);
        exit;
    }

    // create data array
    $data = [];
    while ($row = $result->fetch_assoc()) {
    // add availablity if date is selected
      if (!empty($date)) {
        $booked = isAvailable($row['name'], $date, $conn);
        $row['available'] = $booked;
      }
    // add distance is location is selected
      if(!empty($loca)){
        $distance = calcDist($loca,$row['clinic'],$conn);
        $row['distance'] = $distance;
      }
      
      $data[] = $row;
    }

    echo json_encode($data);
    exit;
}


// ---- Location or Clinic and Location selected ----
if (!empty($loca)) {

    // Fetch clinics
    if($clin != 'none'){
      $sql = "SELECT consultants.name AS name, consultants.consultation_fee AS fee, clinics.name AS clinic, specialities.speciality AS spec, ROUND(AVG(reviews.score),1) AS score, clinics.latitude as lat, clinics.longitude as lon FROM consultants JOIN clinics ON consultants.clinic_id = clinics.id JOIN specialities ON consultants.speciality_id = specialities.id JOIN reviews ON consultants.id = reviews.consultant_id WHERE clinics.name = '$clin' GROUP BY consultants.id ORDER BY consultants.name ASC;";
    }else{
      $sql = "SELECT name, latitude AS lat, longitude AS lon FROM clinics";
    }

    $result = mysqli_query($conn, $sql);

    // error detection
    if (!$result || $result->num_rows == 0) {
        echo json_encode(["message" => "No clinics found."]);
        exit;
    }

    // create data array
    if($clin != 'none'){
      $data = [];
      while ($row = $result->fetch_assoc()) {
        // calculate distances from each clinic to entered location
        $distance = calcDist($loca,$row['clinic'],$conn);
        
        // add availability if date is entered
        if(!empty($date)){
          $booked = isAvailable($row['name'], $date, $conn);
        }

        // add objects to data array
        $data[] = [
          'name' => $row['name'],
          'speciality' => $row['spec'],
          'fee' => $row['fee'],
          'score' => $row['score'],
          'clinic' => $row['clinic'],
          'available' => !empty($date) ? $booked : null,
          'distance' => $distance
        ];
      }
      // sort results by distance
          usort($data, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        echo json_encode($data);
        exit;
      
    }else{
        // if only a location is selected then just show the clinics in descending distance order
      $distances = [];
      while ($row = $result->fetch_assoc()) {
        $distance = calcDist($loca,$row['clinic'],$conn);

        $distances[] = [
            'name' => $row['name'],
            'distance' => $distance
        ];
      }

      usort($distances, function($a, $b) {
        return $a['distance'] <=> $b['distance'];
      });

      echo json_encode($distances);
      exit;
    
    }
}

// ----------- Only Clinic selected -----------
if ($clin != 'none') {

    // not sure why the sql format changed for this when I copied the php code from the database
    $sql = "SELECT consultants.name AS name, consultants.consultation_fee AS fee, clinics.name AS clinic, \n"

    . "       specialities.speciality AS spec, ROUND(AVG(reviews.score),1) AS score\n"

    . "FROM consultants\n"

    . "JOIN clinics ON consultants.clinic_id = clinics.id\n"

    . "JOIN specialities ON consultants.speciality_id = specialities.id\n"

    . "JOIN reviews ON consultants.id= reviews.consultant_id\n"

    . "WHERE clinics.name = '$clin'\n"

    . "GROUP BY consultants.id\n"

    . "ORDER BY consultants.name ASC;";

    $result = mysqli_query($conn, $sql);

    // error detection
    if (!$result || $result->num_rows == 0) {
        echo json_encode(["message" => "No consultants found for this clinic."]);
        exit;
    }

    // create data array
    $data = [];
    while ($row = $result->fetch_assoc()) {
      if (!empty($date)) {
        // check availability
        $booked = isAvailable($row['name'], $date, $conn);
        $row['available'] = $booked;
      }
      $data[] = $row;
    }

    echo json_encode($data);
    exit;
}



// ----------- No valid inputs -----------
echo json_encode(["message" => "Please select at least one filter."]);
exit;
?>