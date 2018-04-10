<?php
	require_once(__DIR__ . "/DiscordWebhook.php");

	$webhook_id = "id_you_from_discord";
	$webhook_token = "token_you_got_from_discord";

	if (!isset($_GET['secret']) || $_GET['secret'] !== $webhook_token)
		exit();

	$webhook = new DiscordWebhook($webhook_id, $webhook_token);

	try {
		$webhook->proxy('Bitbucket');
	}
	catch (DiscordWebhookException $e) {
		error_log($e->getMessage());
	}
?>