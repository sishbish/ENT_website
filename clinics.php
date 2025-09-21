

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <title>Clinics Overview</title>

  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f0f0f0;
      font-size: 18px;
    }

    header {
      background-color: #7aadff;
      color: white;
      padding: 6rem 2rem;
      text-align: center;
      font-size: 28px;
      font-weight: bold;
      position: relative;
    }

    .header-container {
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }

    .header-container img.image-logo {
      position: absolute;
      left: 0;
      height: 180px;
      margin-left: 250px;
      margin-top: -80px;
    }

    .header-container h1 {
      margin: 10 auto;
    }

    .container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 1.5rem 2rem;
      background-color: #ffffff;
      border-radius: 20px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }

    th, td {
      text-align: left;
      padding: 12px 16px;
    }

    th {
      background-color: #7aadff;
      color: white;
    }

    tr:nth-child(even) {
      background-color: #f4f8ff;
    }

    tr:hover {
      background-color: #e6f0ff;
    }

    a.btn {
      background-color: #007bff;
      color: white;
      padding: 6px 12px;
      border-radius: 6px;
      text-decoration: none;
      transition: background-color 0.2s;
      display: inline-block;
    }

    a.btn:hover {
      background-color: #0056b3;
    }

    @media (max-width: 768px) {
      body {
        font-size: 16px;
      }

      .container {
        margin: 1rem;
        padding: 1rem;
      }

      th, td {
        padding: 10px;
      }
    }

    .menu-bar {
      position: fixed;
      top: 0;
      width: 100%;
      background-color: rgba(0, 0, 0, 0.7); 
      padding: 15px 0px;
      z-index: 1000;
    }

    .menu-bar ul {
      display: flex;
      justify-content: center; 
      align-items: center;
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

    .chart-container {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      margin-bottom: 2rem;

    }
  </style>
</head>

<body>
  <!-- banner at the top -->
  <header>
    <div class="header-container">
      <a href="enthub.php"><img class="image-logo" src="Ent.png" alt="Logo"></a>
      <h1>Clinics</h1>
    </div>
  </header>

  <!-- navigation bar -->
  <nav class="menu-bar">
    <ul>
      <li><a href="enthub.php">Home</a></li>
      <li><a href="clinics.php">Clinics</a></li>
      <li><a href="consultants.php">Consultants</a></li>
    </ul>
  </nav>

  <?php

    $servername = "sci-mysql"; 
    $username = "coa123edb"; 
    $password = "E4XujVcLcNPhwfBjx-"; 
    $dbname = "coa123edb"; 

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
      die("Connection failed: " . mysqli_connect_error());
    }

    // getting everything from the clinics table
    $sql = "SELECT * FROM `clinics`;";
    $result = mysqli_query($conn, $sql);

    $data = [];
    while ($row = $result->fetch_assoc()) {
      $data[] = $row;
    }

    // getting the longitude and latitude for each clinic
    $locations = [];
    foreach ($data as $entry) {
      $lat = urlencode($entry['latitude']);
      $lon = urlencode($entry['longitude']);

      // get the written location of each clinic using the geocode api
      $url = "https://geocode.maps.co/reverse?lat={$lat}&lon={$lon}&api_key=6806bc24b1610732931070xvza1686d";

      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      $response = curl_exec($curl);
      curl_close($curl);

      // error handling
      if (!$response) {
        echo json_encode(["message" => "Failed to fetch location."]);
        exit;
      }
      $locations[] = $response;
    }

    // for graph data
    $sql = "SELECT clinics.id, clinics.name AS clinicName, AVG(consultantScores.avScore) AS clinicAverageScore FROM ( SELECT consultants.id, consultants.clinic_id, ROUND(AVG(reviews.score), 1) AS avScore FROM consultants JOIN reviews ON reviews.consultant_id = consultants.id GROUP BY consultants.id ) AS consultantScores JOIN clinics ON clinics.id = consultantScores.clinic_id GROUP BY clinics.id;";
    $result = mysqli_query($conn, $sql);

    $clinScoreData = [];
    while ($row1 = $result->fetch_assoc()) {
      $clinScoreData[] = [
        'x' => $row1['clinicAverageScore'],
        'y' => $row1['clinicName']
      ];
    }
  ?>

  <!-- page container -->
  <div class="container">
    <!-- graph container -->
    <div class="chart-container">
      <canvas id="myChart"></canvas>
    </div>

    <!-- table container -->
    <div id='tableCon'></div>
  </div>

  <!-- footer at the bottom -->
  <footer>
    <p>&copy; 2025 ENT Care Hub. All rights reserved.</p>
    <p><a href="mailto:idk@gmail.com" style="color: #ccc;">Contact Us</a></p>
  </footer>

  <script>
    // locs is the entire geocode api response and locas is only the name of the location which is taken from locs
    const locs = <?php echo json_encode($locations); ?>;
    const locas = locs.map(loc => {
      try {
        return JSON.parse(loc).display_name.split(",")[2] || "Unknown";
      } catch {
        return "Unknown";
      }
    });

    // create results table
    $(document).ready(function () {
      let table = $('<table></table>');
      let header = $('<tr></tr>');
      header.append('<th>Name</th>');
      header.append('<th>Location</th>');
      header.append('<th>Car parking</th>');
      header.append('<th>Disabled access</th>');
      header.append('<th>Actions</th>');
      table.append(header);

      const data = <?php echo json_encode($data); ?>;
      for (let i = 0; i < data.length; i++) {
        const row = data[i];
        let tableRow = $('<tr style="cursor:pointer;"></tr>');

        tableRow.click(function () {
          window.location.href = `clinicsProfile.php?name=${encodeURIComponent(row.name)}`;
        });
        tableRow.append(`<td>${row.name}</td>`);
        tableRow.append(`<td>${locas[i]}</td>`);
        tableRow.append(`<td>${row.car_parking}</td>`);
        tableRow.append(`<td>${row.disabled_access}</td>`);
        tableRow.append(`
          <td>
            <a class="btn" href="clinicsProfile.php?name=${encodeURIComponent(row.name)}">View clinic</a>
          </td>
        `);
        table.append(tableRow);
      }

      $('#tableCon').html(table);
    });

    // graph code
  let scoreData = <?php echo json_encode($clinScoreData); ?>;

  const data = {
      datasets: [{
        label: 'Average consultant score by clinic',
        data: scoreData,
      }]
    };

  const ctx = document.getElementById('myChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: data,
    options: {
      indexAxis: 'y',
      responsive: true,
      scales: {
        x: {
          beginAtZero: true,
          max:5,
          title:{
            display:true,
            text: 'Average consultant score'
          },
        },
        y: {
          title: {
            display: true,
            text: 'Clinic'
          }
        }
      }
    }
  });
  </script>
</body>
</html>