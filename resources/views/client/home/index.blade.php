@extends('client.main')

@section('title', 'Trang Chủ')

@section('styles')
    <style>
    </style>
@endsection

@section('header')
    @parent
@endsection

@section('main')
    <div class="row">
    </div>
@endsection

@section('scripts')
    <script>
        function loadData() {
            $.ajax({
                url: "{{ route('courses') }}",
                type: 'GET',
                success: (response) => {
                    row.empty();
                    if (response.success) {
                        let data = response.courses;
                        if (data.length === 0) {
                            row.append(`<idv class='text-center'>KHÔNG CÓ KHÓA HỌC NÀO!</div>`);
                        } else {
                            let courses = '';
                            $.each(data, (index, course) =>
                                courses += card(course.id, course.name, course.thumbnail)
                            );
                            row.append(courses);
                        }
                    } else {
                        row.append(`<idv class='text-center'>${response.message}</div>`);
                    }
                },
                error: (xhr, status, error) => {
                    console.log(`message: ${error}`);
                }
            });
        }

        function card(id, name, thumbnail) {
            return `<div class="col-lg-3 col-md-6 mb-3 mt-3">
                        <div class="card" data-id='${id}'>
                                <img src="${thumbnail}" class="card-img-top" alt="${name}" height="150px"/>
                                <div class="card-body">
                                    <h5 class="card-title">${name}</h5>
                                    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's
                                        content.</p>
                                    <button class="btn btn-primary" id="btn-add-cart" data-mdb-ripple-init>Thêm vào giỏ hàng</button>
                                </div>
                            </div>
                        </div>`;
        }

        _document.on('click', '#btn-add-cart', function() {
            let id = $(this).closest('.card').data('id');
            $.ajax({
                url: "{{ route('carts.add') }}",
                type: 'POST',
                data: {
                    id
                },
                success: (response) => {
                    console.log(response);
                    if (response.success) {} else {}
                    Toast({
                        message: response.message,
                        type: response.type
                    })
                },
                error: (xhr, status, error) => {
                    console.error(`message: ${error}`);
                }
            })
        })

        $(document).ready(function() {
            loadData();
        });
    </script>
@endsection
