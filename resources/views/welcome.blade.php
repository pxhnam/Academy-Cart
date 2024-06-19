<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome</title>
</head>

<body>
    @php

        // echo 'data:image/png;base64,' . $image;
    @endphp
    <img src="{{ $image }}" width="250px" height="250px" alt="">
</body>

</html>
