<?php
# Developpment made by Bruno Carvalho (bruno.carvalho@xrv.pt).
#
# Disclaimer:
# Feel free to use and modify it at your will (even for enterprise use). Just keep my credit and add your's.
#
if (empty($_GET)) {
    header("Location: /../../../index.php");
} elseif(!isset($_GET['key'])) {
    header("Location: /../../../index.php");
} else {
    $key = $_GET['key'];
}

$encrypt_method = "AES-256-CBC";
$secretkey = hash('sha256', 'NextCloudAPI');
$iv = substr(hash('sha256', 'NextCloudModule'), 0, 16);
$url = openssl_decrypt(base64_decode($key), $encrypt_method, $secretkey, 0, $iv);

$ch = curl_init();
curl_setopt ($ch, CURLOPT_URL, $url);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt ($ch, CURLINFO_HEADER_OUT, true);
curl_setopt ($ch, CURLOPT_HTTPHEADER, array('OCS-APIRequest: true', 'Accept: application/json'));
$result = curl_exec($ch);
curl_close($ch);

$resultrequest = json_decode($result,TRUE);

$total = round ((int)$resultrequest['ocs']['data']['quota']['total'] / 1024 / 1024);
$used = round ((int)$resultrequest['ocs']['data']['quota']['used'] / 1024 / 1024);
$free = round ((int)$resultrequest['ocs']['data']['quota']['free'] / 1024 / 1024);

echo '<!DOCTYPE html>
<html>
	<head>
		<style>
			#container {
                position: absolute;
                height: 100%;
                width: 100%;
			}

			.highcharts-figure,
			.highcharts-data-table table {
				min-width: 310px;
				max-width: 800px;
				margin: 0em;
			}

			.highcharts-data-table table {
				font-family: Verdana, sans-serif;
				border-collapse: collapse;
				border: 1px solid #ebebeb;
				margin: 10px auto;
				text-align: center;
				width: 100%;
				max-width: 500px;
			}

			.highcharts-data-table caption {
				padding: 1em 0;
				font-size: 1.2em;
				color: #555;
			}

			.highcharts-data-table th {
				font-weight: 600;
				padding: 0.5em;
			}

			.highcharts-data-table td,
			.highcharts-data-table th,
			.highcharts-data-table caption {
				padding: 0.5em;
			}

			.highcharts-data-table thead tr,
			.highcharts-data-table tr:nth-child(even) {
				background: #f8f8f8;
			}

			.highcharts-data-table tr:hover {
				background: #f1f7ff;
			}
			.wrapperDiv {
                position: relative;
            }
			.table {
				width:98%;
				height:350px !important;
				display:table;
			}
			.table-style {
				border:1px solid;
				padding:10px;
				background-color:transparent;
				margin:0px;
				border-radius:5px;
				-moz-border-radius:5px;
				-webkit-border-radius:5px;
				-o-box-border-radius:5px;
				box-shadow: 0px 0px 0px 0px #0D7BCE;
				-moz-box-shadow: 0px 0px 0px 0px #0D7BCE;
				-webkit-box-shadow: 0px 0px 0px 0px #0D7BCE;
				-o-box-shadow: 0px 0px 0px 0px #0D7BCE;
				line-Height:26px;			
			}
			.row {
				width:98%;
				display:table-row;
			}
			.cell {
				width:24%;
				display:table-cell;
			}
			.cell.empty {
                border: none;
                width: 50%;
            }
			.cell.rowspanned {
                position: absolute;
                width: 50%;
                top: 11px;
                bottom: 11px;
            }
			.cell-style {
                border: 1px solid;
				font-size:12px;
				text-align:left;
				vertical-align:middle;
				letter-spacing:0px;
				word-spacing:0px;
			}
            p {
                padding: 0px 0px 0px 10px;
            }
		</style>
        <script src="https://code.highcharts.com/highcharts.js"></script>
		<script src="https://code.highcharts.com/highcharts-3d.js"></script>
	</head>
	<body>
		<div class="wrapperDiv">
			<div class="table table-style">
				<div class="row">
					<div class="rowspanned cell cell-style">
						<figure class="highcharts-figure">
							<div id="container"></div>
						</figure>
					</div>
					<div class="cell cell-style"><p>Last Login:</p></div>
					<div class="cell cell-style"><p>' . date('Y/m/d H:i:s', $resultrequest['ocs']['data']['lastLogin']/1000) . '</p></div>
				</div>
				<div class="row">
					<div class="empty cell cell-style"></div>
					<div class="cell cell-style"><p>Storage:</p></div>
					<div class="cell cell-style"><p>' . $total . ' Mb</p></div>
				</div>
				<div class="row">
					<div class="empty cell cell-style"></div>
					<div class="cell cell-style"><p>Used:</p></div>
					<div class="cell cell-style"><p>' . $used . ' Mb</p></div>
				</div>
				<div class="row">
					<div class="empty cell cell cell-style"></div>
					<div class="cell cell-style"><p>Free:</p></div>
					<div class="cell cell-style"><p>' . $free . ' Mb</p></div>
				</div>
				<div class="row">
					<div class="empty cell cell cell-style"></div>
					<div class="cell cell-style"><p>% Usage:</p></div>
					<div class="cell cell-style"><p>' . $resultrequest['ocs']['data']['quota']['relative'] . '%</p></div>
				</div>
				<div class="row">
					<div class="empty cell cell cell-style"></div>
					<div class="cell cell-style"><p>Interface Language:</p></div>
					<div class="cell cell-style"><p>' . $resultrequest['ocs']['data']['language'] . '</p></div>
				</div>
			</div>
		</div>
        <script type="text/javascript">
            Highcharts.setOptions({ colors: [\'#b34045\', \'#2d884d\']});
			Highcharts.chart(\'container\', {
				chart: {
                    margin: [0, 0, 0, 0],
                    spacingTop: 0,
                    spacingBottom: 0,
                    spacingLeft: 0,
                    spacingRight: 0,
					type: \'pie\',
					options3d: {
						enabled: true,
						alpha: 45
					}
				},
				title: {
					text: \'\',
				},
                credits: {
                    enabled: false
                },
				plotOptions: {
					pie: {
						innerSize: 100,
						depth: 45
					}
				},
				series: [{
					name: \'Storage Usage\',
					data: [
						[\'Used (Mb)\', ' . $used . '],
						[\'Free (Mb)\', ' . $free . ']

					]
				}]
			});
        </script>
	</body>
</html> 
';
?>