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
}
