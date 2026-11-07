<?php

namespace App;

enum TicketStatus: string
{
    case Open = 'open';
    case Settled = 'settled';
}
