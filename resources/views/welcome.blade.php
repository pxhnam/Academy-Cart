<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome</title>
    {{-- <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/fontawesome-6.5.2/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet"> --}}

</head>

<body>


    <div class="container">
        <form action="/hi" method="POST">
            @csrf()
            <button type="submit">Ok</button>
        </form>
        {{-- <h3>Hi, everyone</h3>
        <h3>Rất yêu quý</h3> --}}

        {{-- <form id="box-form"> --}}

        {{-- <div class="form-floating mb-3">
            <input type="text" class="form-control" name="username" id="username" placeholder="" autofocus>
            <label for="username">Username</label> --}}
        {{-- @method('PUT') --}}
        {{-- @csrf() --}}
        {{-- <button type="submit" class="btn btn-primary">Submit</button> --}}

        {{-- </form> --}}

        {{-- <div id="loading">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div> --}}
        <!-- Toast -->
        {{-- <div id="toast"></div> --}}
        <!-- Toast -->
    </div>

    <script>
        // $.ajaxSetup({
        //     headers: {
        //         "X-CSRF-TOKEN": "{{ csrf_token() }}",
        //     },
        // });
        // $('#box-form').submit(function(e) {
        //     e.preventDefault();
        //     $.ajax({
        //         url: '{{ route('client.product.update') }}',
        //         data: new FormData($(this)[0]),
        //         type: 'POST',
        //         processData: false,
        //         contentType: false,
        //         success: (response) => {
        //             console.log(response);
        //         }
        //     })
        // });

        //'{{ route('client.product.remove', ['id' => 1]) }}'
        // $('button').click(() => {
        //let id = 12;
        //var url = '{{ route('client.product.remove', ':id') }}';
        //url = url.replace(':id', id);
        //     $.ajax({
        //         url: '{{ route('client.product.update') }}',
        //         data: {
        //             id: 1,
        //             username: 'admin',
        //             password: '123'
        //         },
        //         type: 'POST',
        //         success: (response) => {
        //             console.log(response);
        //         }
        //     })
        // });
    </script>
    </div>

</body>

</html>
