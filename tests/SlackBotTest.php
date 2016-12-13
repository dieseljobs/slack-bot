<?php

use TheLHC\SlackBot\SlackBot;
use TheLHC\SlackBot\Tests\TestCase;

class SlackBotTest extends TestCase
{

  public function testCanResolveResponderFromTheContainer()
    {
        $manager = $this->app->make('TheLHC\SlackBot\SlackBot');
        $this->assertInstanceOf(SlackBot::class, $manager);
    }

    public function testSendsChatMessage()
    {
        $this->app['config']->set('slack_bot.token', '##');
        //$this->app['config']->set('slack_bot.username', 'Used Equipment Guide');
        //$this->app['config']->set('slack_bot.emoji_icon', ':ueg:');
        //$this->app['config']->set('slack_bot.server_ip', true);

        $slackBot = $this->app->make('TheLHC\SlackBot\SlackBot');
        $chat = $slackBot->chat('test message', '#ueg-status');
        $this->assertEquals(true, $chat);
    }
}
