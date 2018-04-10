
This a converter proxy for Discord webhooks for PHP. 

Discord only supports GitHub and Slack webhooks out of the box, so I figured I'd make this to support more.

### Supported services ###
* Bitbucket


### Examples ###

See example.php to see how the class should be used.

The core usage can be squeezed into two simple calls.

```
	$webhook = new DiscordWebhook($webhook_id, $webhook_token);
	$webhook->proxy('Bitbucket');
```

### Extending ###

DiscordWebhook class automatically recognizes member functions starting with "convertFrom", 
so adding new services should be relatively easy. 
Just write a new function such as ```convertFromMyService()``` and you should be able to call it 
with ```$webhook->proxy('MyService')```
