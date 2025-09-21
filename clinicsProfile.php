<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <title>Clinic Overview</title>

  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f0f0f0;
      font-size: 18px;
    }

    .container {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 2rem;
      margin-top: 50px;
    }

    .blue-card {
      width: 100%;
      max-width: 700px;
      background-color: #7aadff;
      border-radius: 20px;
      padding: 1.5rem 2rem;
      display: flex;
      gap: 2rem;
      align-items: center;
      margin-bottom: 2rem;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .blue-card img {
      height: 100px;
      border-radius: 10px;
    }

    .info {
      display: flex;
      flex-direction: column;
      gap: 0.6rem;
    }

    .info h2 {
      margin: 0;
      font-size: 24px;
      color: white;
    }

    .info p {
      margin: 0;
      color: white;
    }

    .data-table {
      width: 100%;
      max-width: 700px;
      background-color: #7aadff;
      border-radius: 20px;
      padding: 1.5rem;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      margin-bottom: 2rem;
    }

    .data-table table {
      width: 100%;
      border-collapse: collapse;
    }

    .data-table th, .data-table td {
      text-align: left;
      padding: 12px 20px;
      border-bottom: 1px solid #e0e0e0;
      color: white;
    }

    #map {
      width: 100%;
      max-width: 700px;
      height: 300px;
      margin-bottom: 2rem;
      border-radius: 10px;
    }

    .chart-container {
      width: 100%;
      max-width: 700px;
      margin-bottom: 2rem;
    }

    .bullets {
      display: flex;
      justify-content: center;
      gap: 2rem; 
      vertical-align: middle;
      list-style: none;
    }

    .bullets li::before {
      content: "•";
      color: #cc1212;
      margin-right: 6px;
      vertical-align: middle;
      font-size: 50px;
    }

    .bullets li.green::before {
      color: #12cc44;
    }

    .menu-bar {
      position: fixed;
      top: 0;
      width: 100%;
      background-color: rgba(0, 0, 0, 0.7);
      padding: 15px 30px;
      z-index: 1000;
    }

    .menu-bar ul {
      display: flex;
      justify-content: center;
      list-style: none;
      margin: 0;
      padding: 0;
      gap: 30px;
    }

    .menu-bar li a {
      color: white;
      text-decoration: none;
      font-size: 18px;
    }

    footer {
      background-color: #333;
      color: white;
      text-align: center;
      padding: 15px 10px;
    }

  </style>
</head>
<body>
  

<?php
    if (isset($_GET['name'])) {
        $clinicName = $_GET['name'];

        $servername = "sci-mysql"; 
        $username = "coa123edb"; 
        $password = "E4XujVcLcNPhwfBjx-"; 
        $dbname = "coa123edb"; 
        
        $conn = mysqli_connect($servername, $username, $password, $dbname);
        if (!$conn) {
          die("Connection failed: " . mysqli_connect_error());
        }

        $sql = "SELECT clinics.name AS name, clinics.car_parking AS parking, clinics.disabled_access AS access, clinics.longitude AS lng, clinics.latitude AS lat FROM `clinics` WHERE clinics.name = '$clinicName';";

        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
          $row = $result->fetch_assoc();
        } else {
          echo 'err';
        }
        
        $sql = "SELECT consultants.name AS name, specialities.speciality AS spec FROM `consultants` JOIN specialities ON specialities.id = consultants.speciality_id JOIN clinics ON clinics.id = consultants.clinic_id WHERE clinics.name = '$clinicName';";

        $result = mysqli_query($conn, $sql);

        $data = [];
        while ($row1 = $result->fetch_assoc()) {
          $data[] = $row1;
        }

        $sql = "SELECT distinct specialities.speciality as spec from consultants join specialities on specialities.id = consultants.speciality_id join clinics on clinics.id = consultants.clinic_id where clinics.name = '$clinicName';";

        $result = mysqli_query($conn, $sql);

        $clinData = [];
        while ($row2 = $result->fetch_assoc()) {
          $clinData[] = $row2;
        }

    }
  ?> 

  <!-- navigation bar -->
  <nav class="menu-bar">
      <ul>
        <li><a href="enthub.php">Home</a></li>
        <li><a href="clinics.php">Clinics</a></li>
        <li><a href="consultants.php">Consultants</a></li>
      </ul>
    </nav>

<!-- page container -->
<div class="container">

  
  <!-- profile card container -->
  <div class="blue-card">
    <img src="Adobe%20Express%20-%20file.png">

    <!-- clinic info container -->
    <div class="info">
      <h2 class="nameCon"></h2>
      <p id="parking"></p>
      <p id="access"></p>
    </div>

  </div>

  <!-- listing all the specialities. Specialities that are available at this clinic have green bullet points and the others have red bullet points -->
  <ul class="bullets">
      <li>Otology</li>
      <li>Rhinology</li>
      <li>Laryngology</li>
      <li>Paediatric ENT</li>
      <li>Allergy</li>
      <li>Head and Neck Surgery</li>
    </ul>

  <!-- google maps container -->
  <div id="map"></div>

  <!-- table container -->
  <div class="data-table" id="tableCon"></div>

  
  <!-- bar chart container -->
  <div class="chart-container">
    <canvas id="myChart"></canvas>
  </div>
</div>

<!-- footer at the bottom -->
<footer>
    <p>&copy; 2025 ENT Care Hub. All rights reserved.</p>
    <p><a href="mailto:idk@gmail.com" style="color: #ccc;">Contact Us</a></p>
  </footer>

<script>

  <?php if (isset($row)): ?>
    // create arrays with all the data requested from the database
    let name = <?php echo json_encode($row['name']); ?>;
    let parking = <?php echo json_encode($row['parking']); ?>;
    let access = <?php echo json_encode($row['access']); ?>;

    // add the info for the profile card
    $('.nameCon').text(name);
    $('#parking').text("Car parking: " + parking);
    $('#access').text("Disabled access: " + access);
  <?php else: ?>
    $('.nameCon').text("Clinic not found.");
  <?php endif; ?>

  // code to give the specialites listed a red or green bullet
  let tempSpecs = <?php echo json_encode($clinData); ?>;
  let clinSpecs = tempSpecs.map(obj => obj.spec);

  $('.bullets li').each(function () {
    let text = $(this).text().trim();
    if (clinSpecs.includes(text)) {
      $(this).addClass('green');
    }
  });

  // create the table for the consultants
  $(document).ready(function () {
    let data = <?php echo json_encode($data); ?>;
    let table = $('<table></table>');
    let header = $('<tr></tr>').append('<th>Consultant</th><th>Speciality</th>');
    table.append(header);

    data.forEach(row => {
      let tr = $('<tr></tr>');
      tr.append(`<td><a href="consProfile.php?name=${encodeURIComponent(row.name)}">` + row.name + '</a></td>');
      tr.append('<td>' + row.spec + '</td>');
      table.append(tr);
    });

    $('#tableCon').html(table);
  });

  // graph code
  let data = <?php echo json_encode($data); ?>;
  let specs = data.map(row => row.spec);
  let countMap = {};
  specs.forEach(s => countMap[s] = (countMap[s] || 0) + 1);

  const ctx = document.getElementById('myChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: Object.keys(countMap),
      datasets: [{
        label: 'Consultants',
        data: Object.values(countMap),
        barThickness: 50
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      scales: {
        x: {
          beginAtZero: true,
          max:5,
          title:{
            display:true,
            text: 'Number of Consultants'
          },
          ticks: {
            stepSize: 1
          }
        },
        y: {
          title: {
            display: true,
            text: 'Speciality'
          }
        }
      }
    }
  });

  // google maps code
  function initMap() {
    <?php if (isset($row)): ?>
      let lat = parseFloat(<?php echo json_encode($row['lat']); ?>);
      let lng = parseFloat(<?php echo json_encode($row['lng']); ?>);

      let map = new google.maps.Map(document.getElementById('map'), {
        center: { lat, lng },
        zoom: 10
      });

      new google.maps.Marker({
        position: { lat, lng },
        map: map,
        title: <?php echo json_encode($row['name']); ?>
      });
    <?php endif; ?>
  }
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA02_HcolRVkFrFYN5EnBAz-M1-3lYpNgw&callback=initMap" async defer></script>

</body>
</html>