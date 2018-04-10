<?php
	require_once(__DIR__ . "/DiscordWebhook.php");

	// Discord webhook id and token should be stored somewhere safe.
	// These are here for demonstration purposes.
	$webhook_id = "id_you_got_from_discord";
	$webhook_token = "token_you_got_from_discord";

	// Some form of security is recommended to prevent webhook abuse.
	// Discord relies on the token being secret, so this should be enough for us.
	if (!isset($_GET['secret']) || $_GET['secret'] !== $webhook_token)
		exit();

	$webhook = new DiscordWebhook($webhook_id, $webhook_token);

	try {
		$webhook->proxy('Bitbucket');
	}
	catch (DiscordWebhookException $e) {

		// Log errors. You may want to do something else here.
		error_log($e->getMessage());
	}
?>