# Laravel Slack

This package allows you to easily compose and send Slack messages from Laravel applications

## Installation

Step-1:
```sh
composer require aranyasen/laravel-slack
```
Step-2: Publish the config:
```shell
php artisan vendor:publish --provider=Aranyasen\\LaravelSlack\\SlackServiceProvider
```
It creates `config/laravel-slack.php`.

Step-3:
Add parameters `SLACK_WORKSPACE` and `SLACK_TOKEN` in .env  
(See [ref](To-create-an-OAuth-token-at-Slack) below on how to generate a Slack API token)

### Usage
```php
// Send a simple message to a channel, say "some-channel"
(new SlackNotification())
    ->channel('some-channel')
    ->text("Hello!")
    ->send();

// Send a section (Ref: https://api.slack.com/reference/block-kit/blocks#section)
(new SlackNotification())
    ->channel('some-channel')
    ->section() // Starts a section
    ->fields() // Starts a field in this section
    ->markdown(":fire: @here This is an emergency :fire:")
    ->endFields()
    ->endSection()
    ->send();

// Send a raw JSON block (example from https://api.slack.com/block-kit/building#block_basics)
(new SlackNotification())
    ->channel('some-channel')
    ->block([
      "type" => "section",
      "text" => [
        "type" => "mrkdwn",
        "text" => "New Paid Time Off request from <example.com|Fred Enriquez>\n\n<https://example.com|View request>",
      ],
    ])
    ->send();

// Compose a message and dump the JSON that'll be sent to Slack. Useful for debugging.
(new SlackNotification())
    ->channel('some-channel')
    ->text("Hello!")
    ->dump();
```
#### APIs:
`channel()`   -> Channel  
`header()`    -> Create a header section  
`context()`   -> A small footer text  
`divider()`   -> A horizontal line (like <hr>)  
`section()` / `endSection()` --> A section block  
`lists()`     -> List of items  
`field()` / `endfield()` --> Inside section  
`markdown()`  -> A markdown block, allowed only inside a section  
`block()`     -> Pre-composed block  
`send()`      -> Send to Slack  
`dump()`      -> Dump the final JSON that'd be sent to Slack API

### Testing:
Invoke `SlackNotification::fake()` to ensure HTTP requests to Slack are mocked. Internally it uses Laravel's [Http::fake()](https://laravel.com/docs/10.x/http-client#testing),
so all available `Http::assert*` methods can be used for assertions.
Example:
```php
SlackNotification::fake();
(new SlackNotification())
    ->channel('channel-1')
    ->send();
Http::assertSent(static fn(Request $request) => $request['channel'] === 'channel-1');
```

### References:

#### To create an OAuth token at Slack
* Visit https://api.slack.com/apps
* If no app is present, create an app (you may select "from scratch")
* If the app was created earlier, select the app under *App Name*
* On the left pane, under "Features" click "OAuth & Permissions"
* Under _Scopes_ > _Bot Token Scopes_, click _Add an OAuth Scope_
* Add these scopes: `chat.write` and `chat.write.public`.  
  (note: `channels.read`, `users.read` may be needed in future versions of this package, but not now)
* Click "_reinstall your app_" in the yellow bar that appears above
* In the dropdown "Search for a channel", select a channel. Any channel would do - won't matter now.
* Allow it
* Copy the "_Bot User OAuth Token_" and share

#### Links:
* Slack Block reference docs: https://api.slack.com/block-kit
* Emoji cheat-sheet: https://github.com/ikatyang/emoji-cheat-sheet
* Color bar not supported in block kits yet (ref: https://stackoverflow.com/a/74826061/2014868)
