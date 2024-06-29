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
    <div class="row" id="list-courses">
    </div>
@endsection

@section('scripts')
    <script>
        window.addEventListener('resize', function(event) {
            console.log('');
        }, true);
        var listCourses = $('#list-courses');

        function loadData() {
            $.get('/courses', function(response, status) {
                if (status === 'success') {
                    listCourses.empty();
                    if (response.success) {
                        let data = response.courses;
                        if (data.length) {
                            let courses = data.map(course =>
                                card(course.id, course.name, course.thumbnail)
                            ).join('');
                            listCourses.append(courses);
                        } else {
                            listCourses.append(`<idv class='text-center'>KHÔNG CÓ KHÓA HỌC NÀO!</div>`);
                        }
                    } else {
                        listCourses.append(`<idv class='text-center'>${response.message}</div>`);
                    }
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

        $document.on('click', '#btn-add-cart', function() {
            let id = $(this).closest('.card').data('id');
            $.post('/carts/add', {
                id
            }, function(response, status) {
                if (status === 'success') {
                    if (response.message) {
                        Toast({
                            message: response.message,
                            type: response.type
                        });
                    }
                }
            });
        });

        $document.ready(function() {
            loadData();
        });
    </script>
@endsection
