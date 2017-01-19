<?php return [

    /*
    |--------------------------------------------------------------------------
    | Default Channel
    |--------------------------------------------------------------------------
    |
    | This is the default channel to post bot messages on
    |
    */

    'default_channel' => '#channel',

    /*
    |--------------------------------------------------------------------------
    | Username
    |--------------------------------------------------------------------------
    |
    | This will be the displayed username of posting slack bot
    |
    */

    'username' => 'Slack Bot',

    /*
    |--------------------------------------------------------------------------
    | Base Uri
    |--------------------------------------------------------------------------
    |
    | Base uri for slack api endpoint
    |
    */

    'base_uri' => 'https://slack.com/api/',

    /*
    |--------------------------------------------------------------------------
    | Token
    |--------------------------------------------------------------------------
    |
    | Token to pass to Slack API requests
    |
    */

    'token' => 'abcd1234',

    /*
    |--------------------------------------------------------------------------
    | Emoji Icon
    |--------------------------------------------------------------------------
    |
    | Reference to slack emoji icon
    |
    */

    'emoji_icon' => ':grinning:',

    /*
    |--------------------------------------------------------------------------
    | Server IP
    |--------------------------------------------------------------------------
    |
    | Include server public IP in username
    |
    */

    'server_ip' => false,

    /*
    |--------------------------------------------------------------------------
    | Blacklist Ips by Organization
    |--------------------------------------------------------------------------
    |
    | block message if ipInfo lookup returns result with organization from the
    | following.  Or leave blank
    |
    */

    'blacklist_providers' => [],
];
