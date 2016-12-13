# :Slack Bot

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

## Install

Via Composer

``` bash
$ composer require thelhc/lhc-slack-bot
```

After installing, add the ServiceProvider to the providers array in `config/app.php.`

```
TheLHC\SlackBot\SlackBotServiceProvider::class
```

And add the Facade to the aliases array

```
'SlackBot'  => TheLHC\SlackBot\Facades\SlackBot::class,
```

## Configuration

First publish vendor configuration file using:

``` bash
$ php artisan vendor:publish
```

Then modify your configuration in `config/slack_bot.php`

## Usage

``` php
use SlackBot;

SlackBot::chat('hello world');
```

## Testing

``` bash
$ composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/thelhc/lhc-slack-bot.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/thelhc/lhc-slack-bot.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/thelhc/lhc-slack-bot
[link-downloads]: https://packagist.org/packages/thelhc/lhc-slack-bot
[link-author]: https://github.com/aaronkaz
