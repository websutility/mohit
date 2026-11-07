<?php

namespace App;

enum RoundStatus: string
{
    case Open = 'open';
    case Spinning = 'spinning';
    case Closed = 'closed';
}
