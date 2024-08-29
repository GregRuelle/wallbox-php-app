<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title>Contrôle Wallbox</title>
	<!-- Bootstrap CSS CDN -->
	<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
	<!-- FontAwesome CSS CDN -->
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
	<!-- Meta tags pour bloquer l'indexation et le suivi des robots -->
	<meta name="robots" content="noindex, nofollow">
	<meta name="googlebot" content="noindex, nofollow">
	<meta name="bingbot" content="noindex, nofollow">
	<meta name="slurp" content="noindex, nofollow">
	<meta name="duckduckbot" content="noindex, nofollow">
	<meta name="baiduspider" content="noindex, nofollow">
	<meta name="yandexbot" content="noindex, nofollow">
	<meta name="msnbot" content="noindex, nofollow">
	<meta name="openai" content="noindex, nofollow">
</head>
<body class="bg-light">

	<div class="container mt-5">
		<h1 class="text-center mb-4">Contrôle Wallbox</h1>

		<?php
		// Configuration des informations d'identification et du chargeur
		$email = 'xxx@xxxx.com'; // Email utilisé pour se connecter au compte Wallbox
		$password = 'xxx'; // Mot de passe utilisé pour se connecter au compte Wallbox
		$charger_id = xxxxxx; // Numéro de série de la Wallbox (les six chiffres après le préfixe SN)
		$response_message = ''; // Variable pour stocker les messages de réponse

		// Tableau de correspondance pour status_id
		$status_explanations = [
			0 => 'Disconnected',
			14 => 'Error',
			15 => 'Error',
			161 => 'Ready',
			162 => 'Ready',
			163 => 'Disconnected',
			164 => 'Waiting',
			165 => 'Locked',
			166 => 'Updating',
			177 => 'Scheduled',
			178 => 'Paused',
			179 => 'Scheduled',
			180 => 'Waiting for car demand',
			181 => 'Waiting for car demand',
			182 => 'Paused',
			183 => 'Waiting in queue by Power Sharing',
			184 => 'Waiting in queue by Power Sharing',
			185 => 'Waiting in queue by Power Boost',
			186 => 'Waiting in queue by Power Boost',
			187 => 'Waiting MID failed',
			188 => 'Waiting MID safety margin exceeded',
			189 => 'Waiting in queue by Eco-Smart',
			193 => 'Charging',
			194 => 'Charging',
			195 => 'Charging',
			196 => 'Discharging',
			209 => 'Locked',
			210 => 'Locked - Car connected'
		];

		// Récupération du token pour l'authentification
		function getToken($email, $password) {
			$authHeader = 'Authorization: Basic ' . base64_encode($email . ':' . $password);
			$ch = curl_init('https://api.wall-box.com/auth/token/user');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				$authHeader,
				'Accept: application/json, text/plain, */*',
				'Content-Type: application/json;charset=utf-8'
			]);

			$response = curl_exec($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			if ($http_code === 200) {
				$response_data = json_decode($response, true);
				return $response_data['jwt'] ?? null;
			}
			return null;
		}

		// Exécution des actions de configuration du Wallbox (lock, unlock, set current)
		function executeConfigAction($token, $charger_id, $data) {
			$config_url = 'https://api.wall-box.com/chargers/config/' . $charger_id;
			$ch = curl_init($config_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Authorization: Bearer ' . $token,
				'Accept: application/json, text/plain, */*',
				'Content-Type: application/json;charset=utf-8'
			]);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			$response = curl_exec($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			if ($http_code === 200) {
				return '<div class="bg-light p-3 rounded mt-3"><pre>' . json_encode(json_decode($response), JSON_PRETTY_PRINT) . '</pre></div>';
			} else {
				return '<div class="alert alert-danger">Erreur lors de l\'exécution de l\'action : ' . htmlspecialchars($response) . '</div>';
			}
		}

		// Exécution des actions à distance (pause, reprise, redémarrage)
		function executeRemoteAction($token, $charger_id, $action_value) {
			$remote_action_url = 'https://api.wall-box.com/v3/chargers/' . $charger_id . '/remote-action';
			$ch = curl_init($remote_action_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Authorization: Bearer ' . $token,
				'Accept: application/json, text/plain, */*',
				'Content-Type: application/json;charset=utf-8'
			]);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['action' => $action_value]));
			$response = curl_exec($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			if ($http_code === 200) {
				return '<div class="bg-light p-3 rounded mt-3"><pre>' . json_encode(json_decode($response), JSON_PRETTY_PRINT) . '</pre></div>';
			} else {
				return '<div class="alert alert-danger">Erreur lors de l\'exécution de l\'action : ' . htmlspecialchars($response) . '</div>';
			}
		}

		// Récupération du statut détaillé du Wallbox
		function getChargerStatus($token, $charger_id, $status_explanations) {
			$status_url = 'https://api.wall-box.com/chargers/status/' . $charger_id;
			$ch = curl_init($status_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPGET, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Authorization: Bearer ' . $token,
				'Accept: application/json, text/plain, */*',
				'Content-Type: application/json;charset=utf-8'
			]);
			$response = curl_exec($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			if ($http_code === 200) {
				$data = json_decode($response, true);
				$charging_power = $data['charging_power'] ?? 'N/A';
				$status_id = $data['status_id'] ?? 'N/A';
				$status_description = $status_explanations[$status_id] ?? 'Unknown Status';
				$locked = isset($data['config_data']['locked']) ? ($data['config_data']['locked'] ? 'Yes' : 'No') : 'N/A';
				$max_charging_current = $data['config_data']['max_charging_current'] ?? 'N/A';
				$ecosmart = $data['config_data']['ecosmart']['mode'] ?? 'N/A';
				$current_mode = $data['current_mode'] ?? 'N/A';
				$updateAvailable = $data['config_data']['software']['updateAvailable'] ?? false;
				$currentVersion = $data['config_data']['software']['currentVersion'] ?? 'Unknown';

				// Explication du mode Eco-Smart
				$ecosmart_mode = ($ecosmart == 0) ? 'Eco Mode' : 'Full Green';

				$software_status = $updateAvailable ? "Une nouvelle version est disponible." : "La borne est à jour (Version : $currentVersion).";

				// Affichage des paramètres mis en évidence
				$output = "<div class='bg-light p-3 rounded mt-3'>";
				$output .= "<h5>Statut du Wallbox</h5>";
				$output .= "<p class='mb-0'><strong>Puissance de charge :</strong> {$charging_power} kW</p>";
				$output .= "<p class='mb-0'><strong>ID Statut :</strong> {$status_id} - {$status_description}</p>";
				$output .= "<p class='mb-0'><strong>Verrouillé :</strong> {$locked}</p>";
				$output .= "<p class='mb-0'><strong>Courant de charge maximum :</strong> {$max_charging_current} A</p>";
				$output .= "<p class='mb-0'><strong>Eco-Smart :</strong> {$ecosmart_mode}</p>";
				$output .= "<p class='mb-0'><strong>Mode actuel :</strong> {$current_mode}</p>";
				$output .= "<p class='mb-0'><strong>Mise à jour du logiciel :</strong> {$software_status}</p>";
				$output .= "<hr>";
				$output .= "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
				$output .= "</div>";

				return $output;
			} else {
				return '<div class="alert alert-danger">Erreur lors de la récupération du statut : ' . htmlspecialchars($response) . '</div>';
			}
		}

		// Gestion de la requête POST
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$action = $_POST['action'];
			$maxChargingCurrent = isset($_POST['maxChargingCurrent']) ? (int)$_POST['maxChargingCurrent'] : null;

			// Récupération du token
			$token = getToken($email, $password);

			if ($token) {
				// Gestion des différentes actions en fonction du bouton cliqué
				switch ($action) {
					case 'lock':
						$response_message = executeConfigAction($token, $charger_id, ['locked' => 1]);
						break;

					case 'unlock':
						$response_message = executeConfigAction($token, $charger_id, ['locked' => 0]);
						break;

					case 'setCurrent':
						if ($maxChargingCurrent !== null && $maxChargingCurrent >= 6 && $maxChargingCurrent <= 32) {
							$response_message = executeConfigAction($token, $charger_id, ['max_charging_current' => $maxChargingCurrent]);
						} else {
							$response_message = '<div class="alert alert-warning">Valeur de courant de charge invalide. Doit être entre 6 et 32.</div>';
						}
						break;

					case 'pause':
						$response_message = executeRemoteAction($token, $charger_id, 2);
						break;

					case 'resume':
						$response_message = executeRemoteAction($token, $charger_id, 1);
						break;

					case 'resume_eco':
						$response_message = executeRemoteAction($token, $charger_id, 9);
						break;

					case 'update':
						$response_message = executeRemoteAction($token, $charger_id, 5);
						break;

					case 'reboot':
						$response_message = executeRemoteAction($token, $charger_id, 3);
						break;

					case 'status':
						$response_message = getChargerStatus($token, $charger_id, $status_explanations);
						break;

					default:
						$response_message = '<div class="alert alert-danger">Action inconnue.</div>';
						break;
				}
			} else {
				$response_message = '<div class="alert alert-danger">Échec de l\'obtention du token.</div>';
			}
		}
		?>

		<!-- Formulaire pour les actions -->
		<form method="post" class="p-4 bg-white rounded shadow-sm">
			<div class="form-group">
				<button type="submit" name="action" value="lock" class="btn btn-danger mr-2">
					<i class="fas fa-lock"></i> Verrouiller
				</button>
				<button type="submit" name="action" value="unlock" class="btn btn-success">
					<i class="fas fa-unlock"></i> Déverrouiller
				</button>
				<button type="submit" name="action" value="update" class="btn btn-info mr-2">
					<i class="fas fa-arrow-up"></i> Mettre à jour
				</button>
				<button type="submit" name="action" value="reboot" class="btn btn-secondary mr-2">
					<i class="fas fa-sync-alt"></i> Redémarrer la Wallbox
				</button>
				<button type="submit" name="action" value="status" class="btn btn-dark">
					<i class="fas fa-info-circle"></i> Statut détaillé
				</button>
			</div>
			<div class="form-group">
				<label for="maxChargingCurrent">Définir le courant de charge maximum (6-32A) :</label>
				<div class="input-group mb-3">
					<input type="number" id="maxChargingCurrent" name="maxChargingCurrent" class="form-control" min="6" max="32">
					<div class="input-group-append">
						<button type="submit" name="action" value="setCurrent" class="btn btn-primary">
							<i class="fas fa-bolt"></i> Définir le courant
						</button>
					</div>
				</div>
			</div>
			<div class="form-group">
				<button type="submit" name="action" value="pause" class="btn btn-warning mr-2">
					<i class="fas fa-pause"></i> Mettre en pause
				</button>
				<button type="submit" name="action" value="resume" class="btn btn-info mr-2">
					<i class="fas fa-play"></i> Reprendre la charge
				</button>
				<button type="submit" name="action" value="resume_eco" class="btn btn-success mr-2">
					<i class="fas fa-leaf"></i> Reprendre le mode eco-smart
				</button>
			</div>
		</form>

		<!-- Affichage des réponses en-dessous du formulaire -->
		<?php if (!empty($response_message)) echo $response_message; ?>

	</div>

	<!-- Bootstrap JS and dependencies -->
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
