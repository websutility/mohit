<?php

namespace App;

enum BetStatus: string
{
    case Pending = 'pending';
    case Won = 'won';
    case Lost = 'lost';
}
