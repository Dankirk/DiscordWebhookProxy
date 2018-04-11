
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

### Running example.php ###
1. Create webhook on Discord. Take note on webhooks id and token (they are part of the url)
2. Deploy this package to your webserver and put Discord' webhook id and token into example.php
3. On Bitbucket create webhook pointing to https://myserver.com/example.php?service=Bitbucket&secret=my_discord_webhook_token
4. Try pushing something to the Bitbucket repository and see if it gets printed on Discord. Check your error logs if not.


Output should look something like this

![Alt text](DiscordWebhook.PNG?raw=true "Example output")

### Extending ###

DiscordWebhook class automatically recognizes member functions starting with "convertFrom", 
so adding new services should be relatively easy. 
Just write a new function such as ```convertFromMyService()``` and you should be able to call it 
with ```$webhook->proxy('MyService')```
