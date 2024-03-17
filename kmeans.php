<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Mining Nilai</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <h2>K-Means Clustering Data Nilai</h2>
  <div style="width: 500px; height:500px;">
  <canvas id='barChart'></canvas>
  </div>
  <div style="width: 500px; height:500px;">
  <canvas id='lineChart'></canvas>
  </div>
</body>
<script>
<?php
require "vendor/autoload.php";
$source_data = './data/Nilai.csv';
$output_kmeans = 'clustered_data.csv';
$attributes = 9; // attributes of the scores
$data = new \Phpml\Dataset\CsvDataset($source_data,$attributes,true);
$clustering = new \Phpml\Clustering\KMeans(3);
$clusters = $clustering->cluster($data->getSamples());
$columns = implode(", ",array_keys($clusters));
$connect = mysqli_connect("localhost","root","","kmeans_db");
$file= fopen($output_kmeans, 'w');
$sql="TRUNCATE TABLE sorted_kmeans;";
mysqli_query($connect,$sql);
  foreach ($clusters as $key => $cluster){
    foreach($cluster as $data){
      $dataToWrite = [...$data,$key]; //csv 
      $sql="INSERT INTO sorted_kmeans VALUES ('".$data[0]."','".$data[1]."','".$data[2]."','".$data[3]."','".$data[4]."','".$data[5]."','".$data[6]."','".$data[7]."','".$data[8]."','".$key."')";
      mysqli_query($connect,$sql);
      fputcsv($file,$dataToWrite);
    }
  }

fclose($file);

$query = $connect->query("
SELECT COUNT(Tugas_1) as 'Amount',cluster
FROM sorted_kmeans
GROUP BY cluster;
");
$title1 = array();
$amount = array();
foreach($query as $data1){
$title1[]=$data1['cluster'];
$amount[]=$data1['Amount'];
};

$query = $connect->query("
SELECT AVG(Tugas_1) as 'Tugas 1',AVG(Quiz) as 'Quiz',AVG(Praktik) as 'Praktik',AVG(Tugas_4) as 'Tugas 4',AVG(Tugas_5) as 'Tugas 5',AVG(Tugas_6) as 'Tugas 6',AVG(UTS) as 'UTS',AVG(UAS) as 'UAS',AVG(total) as 'Total'
FROM sorted_kmeans
GROUP BY cluster;
");

$lineData = array();
foreach($query as $data2){
$lineData[]=$data2;
// $Quiz[]=$data2['Quiz'];
// $Praktik[]=$data2['Praktik'];
// $Tugas_4[]=$data2['Tugas 4'];
// $Tugas_5[]=$data2['Tugas 5'];
// $Tugas_6[]=$data2['Tugas 6'];
// $UTS[]=$data2['UTS'];
// $UAS[]=$data2['UAS'];
// $Total[]=$data2['Total'];
};

?>
const amountBar=<?php echo json_encode($amount);?>;
const titleBar=<?php echo json_encode($title1);?>;
var data = {
  labels: titleBar,
    datasets: [{
      data: amountBar,
      borderWidth: 1,
      backgroundColor:[
        'rgba(255,99,132)',
        'rgba(54,162,235)',
        'rgba(255,206,86)',
      ]
    }]
};

//config
const config={
  type: 'bar',
data,
options: {
  scales: {
    y: {
      beginAtZero: true,
      title: {
        display: true,
        text: 'Amount of Clusters'
      }
    }
  },
  plugins: {
    legend: {
      display: false
    }
  }
}
};
const barChart = new Chart(
  document.getElementById('barChart'),
  config
);
const line0=<?php echo json_encode($lineData[0]);?>;
const line1=<?php echo json_encode($lineData[1]);?>;
const line2=<?php echo json_encode($lineData[2]);?>;
data = {
  labels: ['Tugas 1','Quiz','Praktik','Tugas 4','Tugas 5','Tugas 6','UTS','UAS','Total'],
    datasets: [{
      label: '0',
      data: line0,
      fill: false,
    borderColor: 'rgba(255,99,132)',
    },
    {
      label: '1',
      data: line1,
      fill: false,
    borderColor: 'rgba(54,162,235)',
    },
    {
      label: '2',
      data: line2,
      fill: false,
    borderColor: 'rgba(255,206,86)',
    },
  ]
};

//config
const configLine={
  type: 'line',
data,
options: {
  scales: {
    y: {
      min:30
    }
  }
}
};
const lineChart = new Chart(
  document.getElementById('lineChart'),
  configLine
);

</script>
</html>