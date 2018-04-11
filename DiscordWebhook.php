<?php

class DiscordWebhookException extends \Exception { }
class DiscordWebhook {

	const DISCORD_BASE_URL = "https://discordapp.com/api/";

	protected $webhook_id, $webhook_token;
	protected $services = [];

	public function __construct($webhook_id, $webhook_token) {

		$this->webhook_id = $webhook_id;
		$this->webhook_token = $webhook_token;

		// Register functions starting with "convertFrom" as services.
		// This means convertFromBitbucket() becomes a service available with name "Bitbucket".
		// This allows serviceConvert('Bitbucket', $data) to be used in application flow.
		$methods = get_class_methods(get_class());
		foreach ($methods as $method) {

			if (mb_strpos($method, 'convertFrom') === 0) {
				$service = mb_substr($method, 11);
				if ($service)
					$this->services[$service] = $method;
			}
		}
	}

	public function hasService($service) {

		return isset($this->services[$service]);
	}

	// Calls a registered member function with $data as it's parameter
	public function serviceConvert($service, $data) {

		if (!$this->hasService($service))
			throw new DiscordWebhookException("DiscordWebhook invalid service: {$service}");

		return call_user_func_array( array(get_class(), $this->services[$service]), [$data]);
	}

	// Converts Bitbucket webhook input into format Discord can understand
	public function convertFromBitbucket($data) {

		$base_link = "https://bitbucket.org/";

		$payload = [
			'embeds' => []
		];

		$repo = $data['repository']['full_name'];
		$url = $base_link . $data['repository']['full_name'];

		$user = [
			"name" => $data['actor']['display_name'],
			"icon_url" => $data['actor']['links']['avatar']['href'],
			"url" => $base_link . $data['actor']['username']
		];

		foreach ($data['push']['changes'] as $i => $change) {

			if ($i >= 4)
				break;

			$branch = ($change['new'] !== null) ? $change['new']['name'] : $change['old']['name'];
			$commits = [];

			foreach ($change["commits"] as $commit) {

				$message = (mb_strlen($commit['message']) > 256) ? mb_substr($commit['message'], 0, 255) . '\u2026' : $commit['message'];
				$author = isset($commit['author']['user']) ? $commit['author']['user']['display_name'] : "Unknown";

				$commit_hash = mb_substr($commit['hash'], 0, 7);
				$inlined_msg = str_replace(["\r", "\n"], ' ', $message);

				$commits[] = [
					"name" => "Commit from {$author}",
					"value" =>  "([`{$commit_hash}`]({$commit['links']['html']['href']})) {$inlined_msg}",
					"inline" => false
				];
			}
			$commit_count = count($commits);
			$commits_txt = ($commit_count > 1) ? "commits" : "commit";

			$payload['embeds'][] = [
				"title" => "[{$repo}:{$branch}] {$commit_count} {$commits_txt}",
				"url" => $url,
				"author" => $user,
				"fields" => $commits
			];
		}

		return $payload;
	}

	// Posts data to Discord webhook
	public function postToDiscord($data)
	{

		$url = self::DISCORD_BASE_URL . "/webhooks/{$this->webhook_id}/{$this->webhook_token}";

		$headers = [
			'Content-Type: application/json'
		];

		$curl = curl_init($url);

		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($curl);
	
		if ($errno = curl_errno($curl)) {
			$error_message = curl_error($curl);
			curl_close($curl);
			throw new DiscordWebhookException("DiscordWebhook cURL error ({$errno}):\n {$error_message}");
		}

		// If invalid HTTP status code is returned or json_decode() result was NULL (but not for 204), consider the request failed.
		// Throws a DiscordWebhookException, that should be catched in application flow
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		$decoded_result = json_decode($result, true);

		$allowed_http_codes = [
			200, 	// The request completed successfully
			201, 	// The entity was created successfully
			204, 	// The request completed successfully but returned no content
			304		// The entity was not modified (no action was taken)
		];
		if (!in_array($httpcode, $allowed_http_codes) || ($decoded_result === null && $httpcode !== 204))
			throw new DiscordWebhookException("DiscordWebhook error {$httpcode}:{$result}");

		return $decoded_result;
	}

	// A convenience function that handles everything related to proxying a service webhook to Discord
	public function proxy($service) {

		$input = $this->readJson();

		$converted = $this->serviceConvert($service, $input);

		return $this->postToDiscord($converted);
	}

	// Reads JSON most commonly used by webhook services
	public function readJson() {

		$input = json_decode( file_get_contents( 'php://input' ), true );
		if ($input === false)
			throw new DiscordWebhookException("Invalid JSON read");

		return $input;
	}
}

?>