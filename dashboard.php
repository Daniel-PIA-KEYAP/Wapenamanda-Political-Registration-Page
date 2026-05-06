<?php
session_start();
include 'db.php';

if (!isset($_SESSION['candidate_id'])) {
    header("Location: login.html");
    exit;
}

$constituency     = $_SESSION['constituency'];
$candidateName    = htmlspecialchars($_SESSION['candidate_name'] ?? 'Candidate');

$stmt = $pdo->prepare(
    "SELECT ward, COUNT(*) AS total
     FROM users
     WHERE constituency = ?
     GROUP BY ward
     ORDER BY total DESC"
);
$stmt->execute([$constituency]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$wards  = array_column($data, 'ward');
$counts = array_column($data, 'total');

$totalRegistered = array_sum($counts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Candidate Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
    h1 { color: #2c3e50; }
    .section { border: 1px solid #ccc; padding: 15px; margin-top: 20px; background: #fff; border-radius: 4px; }
    .stats { display: flex; gap: 20px; flex-wrap: wrap; }
    .stat-box { background: #2c3e50; color: #fff; padding: 20px 30px; border-radius: 6px; text-align: center; }
    .stat-box h2 { margin: 0; font-size: 2em; }
    .stat-box p { margin: 5px 0 0; }
    nav a { margin-right: 15px; color: #2c3e50; text-decoration: none; font-weight: bold; }
    nav a:hover { text-decoration: underline; }
    canvas { max-width: 800px; }
  </style>
</head>
<body>
  <h1>Dashboard &mdash; <?php echo htmlspecialchars($constituency); ?> Constituency</h1>
  <p>Welcome, <?php echo $candidateName; ?> | <a href="logout.php">Log Out</a></p>

  <nav>
    <a href="index.html">Voter Registration</a>
    <a href="export.php">Export CSV</a>
  </nav>

  <div class="section">
    <h3>Registration Summary</h3>
    <div class="stats">
      <div class="stat-box">
        <h2><?php echo $totalRegistered; ?></h2>
        <p>Total Registered</p>
      </div>
      <div class="stat-box" style="background:#27ae60;">
        <h2><?php echo htmlspecialchars($constituency); ?></h2>
        <p>Constituency</p>
      </div>
    </div>
  </div>

  <div class="section">
    <h3>Ward Population Breakdown</h3>
    <canvas id="wardChart"></canvas>
  </div>

  <script>
    const wards  = <?php echo json_encode($wards); ?>;
    const counts = <?php echo json_encode($counts); ?>;

    let chart = new Chart(document.getElementById('wardChart'), {
      type: 'bar',
      data: {
        labels: wards,
        datasets: [{
          label: 'Population by Ward',
          data: counts,
          backgroundColor: 'rgba(54,162,235,0.6)',
          borderColor: 'rgba(54,162,235,1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          title: { display: true, text: 'Ward Population Breakdown' }
        },
        scales: {
          y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
      }
    });

    // Live auto-refresh every 5 seconds
    function refreshData() {
      fetch('get_population.php')
        .then(res => res.json())
        .then(data => {
          chart.data.labels = data.wards;
          chart.data.datasets[0].data = data.counts;
          chart.update();
        });
    }
    setInterval(refreshData, 5000);
  </script>
</body>
</html>
