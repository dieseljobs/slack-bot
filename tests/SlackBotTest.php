<?php

use TheLHC\SlackBot\SlackBot;
use TheLHC\SlackBot\Tests\TestCase;

class SlackBotTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->app['config']->set('slack_bot.token', env('SLACK_TOKEN'));
        $this->app['config']->set('slack_bot.username', env('SLACK_USERNAME'));
        $this->app['config']->set('slack_bot.emoji_icon', env('SLACK_EMOJI'));
        $this->app['config']->set('slack_bot.default_channel', env('SLACK_CHANNEL'));
        $this->app['config']->set('slack_bot.server_ip', true);
    }

    public function testCanResolveResponderFromTheContainer()
    {

        $manager = $this->app->make('TheLHC\SlackBot\SlackBot');
        $this->assertInstanceOf(SlackBot::class, $manager);
    }

    public function testSendsChatMessage()
    {
        $slackBot = $this->app->make('TheLHC\SlackBot\SlackBot');
        $chat = $slackBot->chat('test message');
        $this->assertEquals(true, $chat);
    }

    public function testSendsChatMessageWithIpLink()
    {
        $slackBot = $this->app->make('TheLHC\SlackBot\SlackBot');
        $chat = $slackBot->chat('test visitor ip http://www.ip-tracker.org/locator/ip-lookup.php?ip=72.224.209.56');
        $this->assertEquals(true, $chat);
    }

    public function testSendsChatMessageWithIpLinkBlocked()
    {
        $this->app['config']->set('slack_bot.blacklist_providers', ['Amazon.com', 'Digital Ocean']);
        $slackBot = $this->app->make('TheLHC\SlackBot\SlackBot');
        $chat = $slackBot->chat('test visitor ip http://www.ip-tracker.org/locator/ip-lookup.php?ip=52.203.61.254');
        $this->assertEquals(true, $chat);
    }

    public function testSendsWebhookMessage()
    {
        $this->app['config']->set('slack_bot.token', null);
        $this->app['config']->set('slack_bot.webhook', env('SLACK_WEBHOOK'));
        # $this->app['config']->set('slack_bot.username', env('SLACK_USERNAME'));
        # $this->app['config']->set('slack_bot.emoji_icon', env('SLACK_EMOJI'));
        $slackBot = $this->app->make('TheLHC\SlackBot\SlackBot');
        $chat = $slackBot->chat('test message', env('SLACK_WEBHOOK_CHANNEL'));
    }
}
