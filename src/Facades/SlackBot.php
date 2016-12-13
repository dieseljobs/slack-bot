<?php namespace TheLHC\SlackBot\Facades;

use Illuminate\Support\Facades\Facade;

class SlackBot extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
      return 'TheLHC\SlackBot\SlackBot';
    }

}
