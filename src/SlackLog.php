<?php

namespace TheLHC\SlackBot;

use Illuminate\Database\Eloquent\Model;

class SlackLog extends Model
{
    protected $table = 'slack_log';
    protected $fillable = ['channel', 'message', 'q'];
}
