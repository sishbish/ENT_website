<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ENT Care Hub</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <style>
    body {
      margin: 0;
      background-color: #f0f0f0;
      font-size: 18px;
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .page-wrapper {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    main {
      flex: 1;
      padding: 20px;
    }

    .blue-rectangle {
      width: 90%;
      max-width: 1300px;
      min-height: 180px;
      background-color: #7aadff;
      border-radius: 20px;
      margin: 20px auto;
      padding: 1rem 2rem;
      display: flex;
      flex-wrap: wrap;
      gap: 1.5rem;
      justify-content: space-between;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      flex: 1 1 200px;
      min-width: 180px;
    }

    label {
      margin-bottom: 0.3rem;
      font-weight: bold;
    }

    input[type="text"],
    input[type="date"],
    select {
      padding: 6px;
      font-size: 16px;
      border-radius: 4px;
      border: 1px solid #ccc;
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
      width: 90%;
      max-width: 1300px;    
      border-collapse: collapse;
      margin: 1rem auto; 
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

    .image-background {
      position: absolute;
      width: 100%;
      z-index: -1;
      overflow: hidden;
    }

    .image-background img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .image-logo {
      width: 10%;
      margin: 75px 0 0 220px;
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

    button[type="submit"] {
      padding: 10px 20px;
      font-size: 16px;
      background-color: #005fcc;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      margin-top: 10px;
    }

    .note {
      font-size: 18px;
      margin-top: 30px;
    }

    .search-instructions {
      width: 100%;
      text-align: center;
      margin-top: 1.5rem;
      padding: 1rem;
      background-color: #e9f2ff;
      border-radius: 12px;
      font-size: 17px;
      line-height: 1.6;
      color: #003366;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .search-instructions ul {
      list-style: none;
      padding-left: 0;
      margin-top: 0.5rem;
    }

    .search-instructions li::before {
      content: "•";
      color: #007bff;
      margin-right: 8px;
    }
  </style>
</head>

<body>
  <!-- page container -->
  <div class="page-wrapper">
    <!-- banner image container -->
    <div class="image-background">
      <img src="http://lh0131.sci-project.lboro.ac.uk/f420588enthub/ENT-scaled-pikl9ktmc45uy6vl4vegdrfyrqbzhoe77520spmi88.jpg" alt="Background Image">
    </div>

    <!-- logo image -->
    <img class="image-logo" src="http://lh0131.sci-project.lboro.ac.uk/f420588enthub/Ent.png" alt="Logo">

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
    ?>

    <!-- blue rectangle with all the inputs -->
    <div class="blue-rectangle">
      <form id="main" method="POST" style="width: 100%; display: flex; flex-wrap: wrap; gap: 1.5rem;">
        <!-- Speciality dropdown -->
        <div class="form-group">
          <label for="speciality">Speciality:</label>
          <select name="speciality">
            <option value="none">None</option>
            <?php
            // get all the specialites for the dropdown
              $result = mysqli_query($conn, "SELECT speciality FROM specialities;");
              if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                  echo '<option value="' . htmlspecialchars($row['speciality']) . '">' . htmlspecialchars($row['speciality']) . '</option>';
                }
              } else {
                echo '<option disabled>No specialities found</option>';
              }
            ?>
          </select>
        </div>

        <p class="note">+ any details</p>

        <!-- Consultant dropdown -->
        <div class="form-group">
          <label for="consultant">Consultant:</label>
          <select name="consultant">
            <option value="none">None</option>
            <?php
              $result = mysqli_query($conn, "SELECT name FROM consultants;");
              if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                  echo '<option value="' . htmlspecialchars($row['name']) . '">' . htmlspecialchars($row['name']) . '</option>';
                }
              } else {
                echo '<option disabled>No consultants found</option>';
              }
            ?>
          </select>
        </div>

        <!-- Clinic dropdown -->
        <div class="form-group">
          <label for="clinic">Clinic:</label>
          <select name="clinic">
            <option value="none">None</option>
            <?php
              $result = mysqli_query($conn, "SELECT name FROM clinics;");
              if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                  echo '<option value="' . htmlspecialchars($row['name']) . '">' . htmlspecialchars($row['name']) . '</option>';
                }
              } else {
                echo '<option disabled>No clinics found</option>';
              }
            ?>
          </select>
        </div>

        <!-- Location input box -->
        <div class="form-group">
          <label for="location">Town/Postcode:</label>
          <input id="location" type="text" name="location" placeholder="town/postcode" />
        </div>

        <!-- Date input box -->
        <div class="form-group">
          <label for="date">Date:</label>
          <input type="date" id="date" name="date">
        </div>

        <!-- Form submit button -->
        <div class="form-group" style="flex: 1 1 100%;">
          <button type="submit" onclick="showBox()">Search</button>
        </div>
      </form>
    </div>

    <!-- instructions -->
    <div class="search-instructions">
      <p><strong>Use the search tool above to find consultants.</strong></p>
      <p>You can search based on:</p>
      <ul>
        <li>Speciality</li>
        <li>Consultant</li>
        <li>Clinic</li>
        <li>Location</li>
        <li>Date</li>
      </ul>
    </div>

    <!-- makes it so the table div is not shown until something is searched -->
    <div id="container" class="container" style="display: none;">

    <!-- table div -->
    <div id="tableCon" class="table"></div>
    </div>

    <?php
    // get the long and tal for all the clinics
      $result = mysqli_query($conn, "SELECT name, longitude, latitude FROM clinics;");
      $mapData = [];
      if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
          $mapData[] = $row;
        }
      }
    ?>

    <!-- map div to show the google map with all the clinic locations -->
    <div id="map" style="width: 100%; height: 500px;"></div>
  </div>

  <!-- footer. serves no purpose just looks nice -->
  <footer>
    <p>&copy; 2025 ENT Care Hub. All rights reserved.</p>
    <p><a href="mailto:idk@gmail.com" style="color: #ccc;">Contact Us</a></p>
  </footer>

  <script>
    // hide result box until something is searched
    function showBox() {
      const div = document.getElementById("container");
      div.style.display = "block"
    }

    // send search data to the results.php file to be processed. This makes sure data is processed asynchronously
    document.getElementById('main').addEventListener('submit', function(event) {
      event.preventDefault();
      const formData = new FormData(this);

      fetch('results.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => createTable(data))
      .catch(err => console.error('Error:', err));
    });

    // makes the results table using jquery
    function createTable(data) {
      $('.blue-rectangle p').remove();

      if ("message" in data) {
        $('.blue-rectangle').append($('<p>').text(data.message));
        return;
      }

      let table = $('<table></table>');
      // table headers
      let header = $('<tr></tr>')
        .append('<th>Name</th><th>Speciality</th><th>Fee</th><th>Rating</th><th>Clinic</th>');

      // add distance and availability headers if they are searched for
      if ("distance" in data[0]) header.append('<th>Distance</th>');
      if ("available" in data[0]) header.append('<th>Availability</th>');

      // extra empty headers to make the header bar cover he entire table
      header.append('<th></th>');
      header.append('<th></th>');
      table.append(header);

      // add the table data
      data.forEach(row => {
        let tr = $('<tr></tr>')
          .append(`<td>${row.name}</td>`)
          .append(`<td>${row.spec}</td>`)
          .append(`<td>£${row.fee}</td>`)
          .append(`<td>${row.score}</td>`)
          .append(`<td><a href='clinicsProfile.php?name=${encodeURIComponent(row.clinic)}'>${row.clinic}</a></td>`);

        if ("distance" in row) tr.append(`<td>${row.distance} km</td>`);

        if ("available" in row) {
          let status = row.available
            ? "<span style='color: green; font-weight: bold;'>Available</span>"
            : "<span style='color: red; font-weight: bold;'>Not Available</span>";
          tr.append(`<td>${status}</td>`);
        }

        tr.append(`<td><a class="btn" href='consSchedule.php?name=${encodeURIComponent(row.name)}'>Schedule</a></td>`);
        tr.append(`<td><a class="btn" href="consProfile.php?name=${encodeURIComponent(row.name)}">Profile</a></td>`);


        table.append(tr);



      });

      $('#tableCon').html(table);
    }

    function initMap() {
      const mapData = <?php echo json_encode($mapData); ?>;
      if (mapData.length === 0) return;

      const map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 0, lng: 0 },
        zoom: 10
      });

      const bounds = new google.maps.LatLngBounds();

      mapData.forEach(location => {
        const position = { lat: parseFloat(location.latitude), lng: parseFloat(location.longitude) };
        new google.maps.Marker({ position, map, title: location.name });
        bounds.extend(position);
      });

      map.fitBounds(bounds);
    }
  </script>

  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA02_HcolRVkFrFYN5EnBAz-M1-3lYpNgw&callback=initMap" async defer></script>
</body>
</html>