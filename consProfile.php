<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Consultant Profile</title>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      color: #333;
    }

    .container {
      max-width: 1200px;
      margin: auto;
      padding: 2rem;
      margin-top: 50px;
    }

    .profile-card {
      display: flex;
      background-color: #7aadff;
      border-radius: 16px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      gap: 1.5rem;
      align-items: center;
      flex-wrap: wrap;
    }

    .profile-card img {
      height: 100px;
      width: 100px;
      border-radius: 12px;
      object-fit: cover;
    }

    .profile-details h2 {
      margin-top: 0;
      margin-bottom: 0.5rem;
    }

    .profile-details p {
      margin: 0.3rem 0;
    }

    .table-wrapper {
      background-color: #ffffff;
      border-radius: 12px;
      padding: 1rem;
      margin-bottom: 2rem;
      overflow-x: auto;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    table th, table td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    table th {
      background-color: #e2ecff;
    }

    .chart-container {
      margin-bottom: 2rem;
      background: #fff;
      border-radius: 12px;
      padding: 1rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    #map {
      width: 100%;
      height: 300px;
      border-radius: 12px;
    }

    @media (max-width: 768px) {
      .profile-card {
        flex-direction: column;
        align-items: flex-start;
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
  </style>
</head>
<body>

<nav class="menu-bar">
      <ul>
        <li><a href="enthub.php">Home</a></li>
        <li><a href="clinics.php">Clinics</a></li>
        <li><a href="consultants.php">Consultants</a></li>
      </ul>
    </nav>

<div class="container">

  <?php
    if (isset($_GET['name'])) {
        $consultantName = $_GET['name'];

        $servername = "sci-mysql";
        $username = "coa123edb";
        $password = "E4XujVcLcNPhwfBjx-";
        $dbname = "coa123edb";

        $conn = mysqli_connect($servername, $username, $password, $dbname);
        if (!$conn) {
          die("Connection failed: " . mysqli_connect_error());
        }

        $sql = "SELECT consultants.name AS name, specialities.speciality AS spec, specialities.description AS specDesc, consultants.consultation_fee AS fee, clinics.name AS clinic, clinics.longitude AS lng, clinics.latitude AS lat, ROUND(AVG(reviews.score),1) AS avScore FROM consultants JOIN specialities ON specialities.id = consultants.speciality_id JOIN clinics ON clinics.id = consultants.clinic_id JOIN reviews ON reviews.consultant_id = consultants.id WHERE consultants.name = '$consultantName' GROUP BY consultants.name, spec, specDesc, fee, clinic, lng, lat;";

        $result = mysqli_query($conn, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
          $row = $result->fetch_assoc();
        } else {
          echo '<p>No data found.</p>';
          exit;
        }

        $sql = "SELECT reviews.feedback AS feedback, reviews.recommend AS recommend, reviews.score AS score FROM reviews JOIN consultants ON consultants.id = reviews.consultant_id WHERE consultants.name = '$consultantName';";
        $result = mysqli_query($conn, $sql);

        $data = [];
        while ($row1 = $result->fetch_assoc()) {
          $data[] = $row1;
        }
    }
  ?>

  <!-- profile card -->
  <section class="profile-card">
    <img src="blank-profile-picture-973460_960_720.webp" alt="Profile Picture" />
    <div class="profile-details">
      <h2><?php echo $row['name']; ?></h2>
      <p><strong>Speciality:</strong> <?php echo $row['spec'] . ': ' . $row['specDesc']; ?></p>
      <p><strong>Fee:</strong> £<?php echo $row['fee']; ?></p>
      <p><strong>Clinic:</strong> <?php echo $row['clinic']; ?></p>
      <p><strong>Average Score:</strong> <?php echo $row['avScore']; ?>/5</p>
    </div>
  </section>

  <!-- table container -->
  <section class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Score</th>
          <th>Feedback</th>
          <th>Recommend</th>
        </tr>
      </thead>
      <tbody>
        <!-- add data to the table -->
        <?php foreach ($data as $review): ?>
          <tr>
            <td><?php echo $review['score']; ?></td>
            <td><?php echo $review['feedback']; ?></td>
            <td><?php echo $review['recommend']; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <!-- graph container -->
  <section class="chart-container">
    <canvas id="myChart"></canvas>
  </section>

  <!-- map container -->
  <section class="map-section">
    <div id="map"></div>
  </section>

</div>

<!-- footer at the bottom -->
<footer>
    <p>&copy; 2025 ENT Care Hub. All rights reserved.</p>
    <p><a href="mailto:idk@gmail.com" style="color: #ccc;">Contact Us</a></p>
  </footer>

<script>
  const data = <?php echo json_encode($data); ?>;

  // counts how many times each score occurs and creates an object with the score and the total number of occurances
  const scoreCounts = data.reduce((acc, review) => {
    acc[review.score] = (acc[review.score] || 0) + 1;
    return acc;
  }, {});

  // split object into the socre and the number of times the score is given
  const labels = Object.keys(scoreCounts);
  const counts = Object.values(scoreCounts);

  // create bar chart
  const ctx = document.getElementById('myChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Score Frequency',
        data: counts,
        barThickness: 20
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      scales: {
        x: {
          beginAtZero: true,
          title:{
            display:true,
            text: 'Number of Reviews'
          },
          ticks: {
            stepSize: 1
          }
        },
        y: {
          title: {
            display: true,
            text: 'Score'
          }
        }
      }
    }
  });

// code for the google maps
  function initMap() {
    const lat = parseFloat("<?php echo $row['lat']; ?>");
    const lng = parseFloat("<?php echo $row['lng']; ?>");

    const map = new google.maps.Map(document.getElementById('map'), {
      zoom: 10,
      center: { lat: lat, lng: lng }
    });

    new google.maps.Marker({
      position: { lat: lat, lng: lng },
      map: map,
      title: "<?php echo $row['clinic']; ?>"
    });
  }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA02_HcolRVkFrFYN5EnBAz-M1-3lYpNgw&callback=initMap" async defer></script>

</body>
</html>