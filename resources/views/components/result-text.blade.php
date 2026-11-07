@props(['result' => null])
@php
    $result = is_array($result) ? $result : [];
@endphp
@if (isset($result['jodi']))
    {{ $result['jodi'] }}
@elseif (isset($result['digit']))
    {{ $result['digit'] }}
@elseif (isset($result['triple']))
    {{ $result['triple'] }}
@elseif (isset($result['horse']))
    {{ $result['number'] }} {{ $result['horse'] }}
@elseif (isset($result['A']))
    A{{ $result['A'] }} B{{ $result['B'] }} C{{ $result['C'] }}{{ ! empty($result['jackpot']) ? ' JP' : '' }}
@else
    —
@endif
