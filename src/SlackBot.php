<?php

namespace TheLHC\SlackBot;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SlackBot
{

    /**
     * Instance of GuzzleHttp Client with base_uri configured
     *
     * @var GuzzleHttp\Client
     */
    private $client;

    /**
     * Default channel to post messages
     *
     * @var string
     */
    private $defaultChannel;

    /**
     * Username to send messages under
     *
     * @var string
     */
    private $username;

    /**
     * Slack API token
     *
     * @var string
     */
    private $token;

    /**
     * Emoji icon
     *
     * @var string
     */
    private $emoji;

    /**
     * Setup new instance with configuration
     *
     * @param Array $config
     */
    public function __construct(Array $config)
    {
        $this->defaultChannel = $config['default_channel'];
        $this->token = $config['token'];
        $this->username = $config['username'];
        // append server IP to title if specified
        if ($config['server_ip']) {
            $ipClient = new Client();
            $ip = trim($ipClient->request('GET', 'http://checkip.amazonaws.com/')->getBody());
            $this->username .= " [{$ip}]";
        }
        $this->emoji = $config['emoji_icon'];
        $this->client = new Client(['base_uri' => $config['base_uri']]);
    }

    /**
     * Send chat message to slack account
     *
     * @param  string $message
     * @param  string|null $channel
     * @param  stdClass|null $describeObject
     * @return bool
     */
    public function chat($message, $channel = null, $describeObject = null)
    {
        // throw exception if table doesn't exist
        if (!SlackLog::resolveConnection()->getSchemaBuilder()->hasTable('slack_log')) {
            throw new \Exception("'slack_log' table doesn't exist");
        }
        // create log row as queued item
        $this->createLog($message, $channel, $describeObject);
        // post messages to slack
        $this->postMessages();
        // return true that method was successful
        return true;
    }

    /**
     * Create SlackLog model instance
     *
     * @param  string $message
     * @param  string|null $channel
     * @param  stdClass|null $describeObject
     * @return void
     */
    private function createLog($message, $channel, $describeObject)
    {
        // if array included, iterate through keys and append to message
        if ($describeObject) {
            foreach($describeObject as $key => $value) {
                $message .= "{$key}: {$value}\n";
            }
        }
        SlackLog::create([
            'channel'   => ($channel) ? $channel : $this->defaultChannel,
            'message'   => $message,
            'q'         => 1
        ]);
    }

    /**
     * Post all queued slack_log messages
     *
     * @return void
     */
    private function postMessages()
    {
        $logs = SlackLog::where('q', '1')
                      ->orderBy('id', 'asc')
                      ->get();
        foreach($logs as $log) {
            $this->postLogToSlack($log);
        }
    }

    /**
     * Post SlackLog instance to board
     *
     * @param  SlackLog $log
     * @return void
     */
    private function postLogToSlack($log)
    {
        try {
            $response = $this->client->request('POST', 'chat.postMessage',
            [
                'verify'        =>  false,
                'form_params'   =>  [
                    'token'     => $this->token,
                    'username'  => $this->username,
                    'icon_emoji'=> $this->emoji,
                    'channel'   => $log->channel,
                    // send all messages with >>> so it's indented (creates vertical
                    // separation between messages)
                    'text'      => ">>>{$log->message}"
                ]
            ]);
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }

        $status = $response->getStatusCode();
        $body = json_decode($response->getBody());
        //var_dump($body);
        if ($body->ok) {
            // unqueue if successful
            $log->update(['q' => 0]);
            sleep(1);
        } else {
            // if bad authorization throw error
            if (property_exists($body, 'error') && $body->error == "invalid_auth") {
                throw new \Exception("Slack Bot credentials are invalid!");
            }
            if (property_exists($body, 'error') && $body->error == "account_inactive") {
                throw new \Exception("Slack Bot credentials are inactive!");
            }
            // if 429 status code, sleep for a minute (429 = speed limit hit)
            if ($status == 429) {
                set_time_limit(180);
                sleep(60);
            }
        }
    }
}
