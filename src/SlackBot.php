<?php

namespace TheLHC\SlackBot;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use TheLHC\IpInfo\IpInfo;

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
     * Slack webhook
     *
     * @var string
     */
    private $webhook;

    /**
     * Emoji icon
     *
     * @var string
     */
    private $emoji;

    /**
     * Result of IpInfo lookup
     *
     * @var stdClass
     */
    private $ipResult;

    /**
     * Providers to blacklist
     *
     * @var Array
     */
    private $blacklistProviders;

    /**
     * Setup new instance with configuration
     *
     * @param Array $config
     */
    public function __construct(Array $config)
    {
        $this->defaultChannel = $config['default_channel'];
        $this->token = $config['token'];
        $this->webhook = $config['webhook'];
        $this->username = $config['username'];
        // append server IP to title if specified
        if ($config['server_ip']) {
            $ipClient = new Client();
            $ip = trim($ipClient->request('GET', 'http://checkip.amazonaws.com/')->getBody());
            $this->username .= " [{$ip}]";
        }
        $this->emoji = $config['emoji_icon'];
        if ($this->token && !empty($config['base_uri'])) {
            $this->client = new Client(['base_uri' => $config['base_uri']]);
        } else {
            $this->client = new Client();
        }
        $this->blacklistProviders = $config['blacklist_providers'];
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
        // result ipResult
        $this->ipResult = null;
        // set and check message
        $message = $log->message;
        $this->checkMessageIp($message);
        if ($this->postMessageToSlack($message, $log->channel)) {
            // unqueue if successful
            $log->update(['q' => 0]);
            sleep(1);
        }
    }

    /**
     * Check message for ip-tracker link
     * If found, do an IpLookup to get details for IP and append details to message
     *
     * @param  string $message
     * @return void
     */
    private function checkMessageIp(&$message)
    {
        preg_match(
            '#http:\/\/www\.ip-tracker\.org\/locator\/ip-lookup\.php\?ip=(\d+\.\d+\.\d+\.\d+)#i',
            $message,
            $matches
        );
        // return if no matches
        if (empty($matches[1])) return;
        $ipInfo = new IpInfo(new \TheLHC\IpInfo\IpInfoRepository());
        $ipLookup = $ipInfo->lookup($matches[1]);
        // return if lookup fails
        if ($ipLookup->status != 'success') return;
        $ip = $ipLookup->results;
        // store results
        $this->ipResult = $ip;
        // append to message
        $location = "";
        if (property_exists($ip, 'city')) $location .= "     location:        *{$ip->city}";
        if (property_exists($ip, 'region')) $location .= ", {$ip->region}";
        if (property_exists($ip, 'region')) $location .= "* ({$ip->country})\r\n";
        if (property_exists($ip, 'org')) $message .= " \r\n     organization: *{$ip->org}*\r\n";
        $message .= $location;
        if (property_exists($ip, 'hostname')) $message .= "     hostname:     *{$ip->hostname}* [_{$ip->ip}_]";
    }

    /**
     * Push message to Slack API
     *
     * @param  string $message
     * @param  string $channel
     * @return boolean
     */
    private function postMessageToSlack($message, $channel)
    {
        if ($this->shouldBlockByProvider()) return true;
        if ($this->webhook) {
            return $this->postMessageToSlackByWebhook($message, $channel);
        } elseif ($this->token) {
            return $this->postMessageToSlackByToken($message, $channel);
        } else {
            throw new \Exception("You must set Slack Bot token or webhook to chat!");
        }
    }

    /**
     * Determine if message should be blocked by its provider
     *
     * @return boolean
     */
    private function shouldBlockByProvider()
    {
        if (!(
            $this->ipResult and
            property_exists($this->ipResult, 'org') and
            !empty($this->blacklistProviders)
        )) {
            return false;
        }
        $pttrn = "#(".implode($this->blacklistProviders, "|").")#i";
        return preg_match($pttrn, $this->ipResult->org);
    }

    /**
     * Post message to slack via webhook
     *
     * @param  String  $message
     * @param  String $channel
     * @return Boolean
     */
    private function postMessageToSlackByWebhook($message, $channel)
    {
        try {
            $response = $this->client->post($this->webhook, [
                'json' => [
                    'username'  => $this->username,
                    'icon_emoji'=> $this->emoji,
                    'channel'   => $channel,
                    // send all messages with >>> so it's indented (creates vertical
                    // separation between messages)
                    'text'      => ">>>{$message}"
                ]
            ]);
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }

        return true;
    }

    /**
     * Post message to slack via API and token
     *
     * @param  String  $message
     * @param  String $channel
     * @return Boolean
     */
    private function postMessageToSlackByToken($message, $channel)
    {
        try {
            $response = $this->client->request('POST', 'chat.postMessage',
            [
                'verify'        =>  false,
                'form_params'   =>  [
                    'token'     => $this->token,
                    'username'  => $this->username,
                    'icon_emoji'=> $this->emoji,
                    'channel'   => $channel,
                    // send all messages with >>> so it's indented (creates vertical
                    // separation between messages)
                    'text'      => ">>>{$message}"
                ]
            ]);
        } catch (RequestException $e) {
            throw new \Exception($e->getMessage());
        }

        $status = $response->getStatusCode();
        $body = json_decode($response->getBody());
        if ($body->ok) {
            return true;
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
                return false;
            }
        }
    }
}
