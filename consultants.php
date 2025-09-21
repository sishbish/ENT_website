<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <title>Consultants Overview</title>

  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f4f6f8;
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
      padding: 1rem;
      background-color: #ffffff;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
      background-color: #f1f5fb;
    }

    tr:hover {
      background-color: #e0efff;
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

  <!-- header at the top -->
  <header>
    <div class="header-container">
      <a href="enthub.php"><img class="image-logo" src="Ent.png" alt="Logo"></a>
      <h1>Consultants</h1>
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


    $sql = "SELECT consultants.name AS name, consultants.consultation_fee AS fee, clinics.name AS clinic, specialities.speciality AS spec 
            FROM consultants 
            JOIN clinics ON clinics.id = consultants.clinic_id 
            JOIN specialities ON specialities.id = consultants.speciality_id;";

    $result = mysqli_query($conn, $sql);

    $data = [];
    while ($row = $result->fetch_assoc()) {
      $data[] = $row;
    }

    $sql = "SELECT consultants.name AS name, consultants.consultation_fee AS fee, ROUND(AVG(reviews.score), 1) AS avScore FROM consultants JOIN reviews ON reviews.consultant_id = consultants.id GROUP BY consultants.id;";

    $result = mysqli_query($conn, $sql);
    $revData = [];
    while ($row1 = $result->fetch_assoc()) {
      $revData[] = [
        'x' => (float)$row1['fee'],
        'y' => (float)$row1['avScore'],
        'label' => $row1['name']
      ];
    }
  ?>

  

  <!-- table container -->
  <div class="container">
    <h3 >Hover over the points to see the name of the consultant</h3>
    <!-- graph container -->
    <div class="chart-container">
      <canvas id="scatter"></canvas>
    </div>

    <div id='tableCon'></div>
  </div>

  <!-- footer at the bottom -->
  <footer>
    <p>&copy; 2025 ENT Care Hub. All rights reserved.</p>
    <p><a href="mailto:idk@gmail.com" style="color: #ccc;">Contact Us</a></p>
  </footer>

  <script>
    // creating table
    $(document).ready(function() {
        let table = $('<table></table>');
        let header = $('<tr></tr>');
        header.append('<th>Name</th>');
        header.append('<th>Speciality</th>');
        header.append('<th>Fee</th>');
        header.append('<th>Clinic</th>');
        header.append('<th></th>');
        table.append(header);

        let data = <?php echo json_encode($data); ?>;

        for (let row of data) {
          let tableRow = $('<tr style="cursor:pointer;"></tr>');

          tableRow.click(function () {
            window.location.href = `consProfile.php?name=${encodeURIComponent(row.name)}`;
          });
          tableRow.append('<td>' + row.name + '</td>');
          tableRow.append('<td>' + row.spec + '</td>');
          tableRow.append('<td>£' + row.fee + '</td>');
          tableRow.append('<td>' + row.clinic + '</td>');
          tableRow.append(`
            <td>
              <a class="btn" href="consProfile.php?name=${encodeURIComponent(row.name)}">Profile</a>
              <a class="btn" href="consSchedule.php?name=${encodeURIComponent(row.name)}" style="margin-left: 6px;">Schedule</a>
            </td>
          `);
          table.append(tableRow);
        }

        $('#tableCon').html(table);
    });

    // graph code
    const scatterData = <?php echo json_encode($revData); ?>;

    const data = {
      datasets: [{
        label: 'Consultant Ratings vs. Fees',
        data: scatterData,
      }]
    };

    const ctx = document.getElementById('scatter').getContext('2d');

    new Chart(ctx, {
    type: 'scatter',
    data: data,
    options: {
      plugins: {
        tooltip: {
          callbacks: {
            // adds a label for each dot on the graph when the user hovers over them
            label: function(context) {
              const name = context.raw.label;
              const fee = context.parsed.x;
              const score = context.parsed.y;
              return `${name}: £${fee}, Score: ${score}/5`;
            }
          }
        }
      },
      scales: {
        x: {
          title: {
            display: true,
            text: 'Consultation Fee (£)'
          }
        },
        y: {
          title: {
            display: true,
            text: 'Average Review Score'
          },
          suggestedMin: 0,
          suggestedMax: 5
        }
      }
    }
  });
  </script>
</body>
</html>