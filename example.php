<?php
	require_once(__DIR__ . "/DiscordWebhook.php");

	// Discord webhook id and token should be stored somewhere safe.
	// These are here for demonstration purposes.
	$webhook_id = "id_you_got_from_discord";
	$webhook_token = "token_you_got_from_discord";

	// Some form of security is recommended to prevent webhook abuse.
	// Discord relies on the token being secret, so this should be enough for us.
	// (We are on https enabled server, right?)
	if (!isset($_GET['secret']) || $_GET['secret'] !== $webhook_token)
		exit();

	$webhook = new DiscordWebhook($webhook_id, $webhook_token);

	// Verify we can support the requested service eg. Bitbucket
	// By using $_GET param we can support any number of service types with this single file.
	// If you need only one service such as Bitbucket, you may skip this 
	// and hardcode 'Bitbucket' as proxy() parameter.
	$service = isset($_GET['service']) ? $_GET['service'] : 'Bitbucket';
	if (!$webhook->hasService($service))
		exit("Specified service not supported");

	try {
		$webhook->proxy($service);
	}
	catch (DiscordWebhookException $e) {

		// Log errors. You may want to do something else here.
		error_log($e->getMessage());
	}
?>