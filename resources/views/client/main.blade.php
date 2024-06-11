<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title') - Ecademy</title>
    <link href="{{ asset('assets/css/mdb.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/fontawesome-6.5.2/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet">
    @yield('styles')
</head>

<body>
    @section('header')
        @include('client.layouts.header')
    @show
    <main>
        <div class="container" id="main">
            @yield('main')
        </div>
    </main>
    @section('footer')
        @include('client.layouts.footer')
    @show
    @section('modal')
        @include('client.components.modal')
    @show
    @section('spinner')
        @include('client.components.spinner')
    @show
    @section('toast')
        @include('client.components.toast')
    @show
    <script src="{{ asset('assets/js/mdb.umd.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    @if (session('notify'))
        <script>
            _document.ready(() => {
                setTimeout(() => {
                    Toast({
                        message: '{{ session('notify.message') }}',
                        type: '{{ session('notify.type') }}'
                    });
                }, 1);
            });
        </script>
    @endif

    <script>
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
            },
        });
    </script>

    @yield('scripts')
</body>

</html>
