@extends('client.main')

@section('title', 'Giỏ Hàng')

@section('styles')
    <style></style>
@endsection

@section('main')
    <section class="h-100 gradient-custom">
        <div class="container py-5">
            <div class="row d-flex justify-content-center my-4">
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        var ids = new Set();
        var codes = new Set();
        let inputSelectCourse, inputSelectCourses, btnCheckout, btnApply, costCourse, costDiscount, totalCourse;
        _document.ready(function() {
            loadData();
        })

        function init() {
            inputSelectCourse = $('input.select-course');
            inputSelectCourses = $('input.select-courses');
            costCourse = $('#costCourse');
            costDiscount = $('#costDiscount');
            totalCourse = $('#totalCourse');
            btnCheckout = $('#btnCheckout');
            btnApply = $('#btn-apply');
        }

        function loadData() {
            $.ajax({
                url: "{{ route('carts.list') }}",
                type: 'GET',
                success: (response) => {
                    row.empty();
                    if (response.success) {
                        let carts = response.data;
                        let data = '';
                        $.each(carts, (index, cart) =>
                            data += boxItem(
                                cart.id,
                                cart.thumbnail,
                                cart.name,
                                cart.lecturer,
                                cart.cost
                            )
                        )
                        row.append(boxCart(data));
                        row.append(boxSummary());
                        Summary();
                        init();
                    } else {
                        row.append(boxEmpty());
                    }
                }
            })
        }

        function updateCourseChecked() {
            ids.clear();
            let selectedCourses = $('input.select-course:checked');
            selectedCourses.each(function() {
                ids.add($(this).closest('tr').data('id'));
            });
            Summary();
        }

        function updateCodeChecked() {
            codes.clear();
            let selectedCode = $('input.select-code:checked');
            selectedCode.each(function() {
                codes.add($(this).val());
            });
        }

        _document.on('click', 'input.select-code', function() {
            let code = $(this).val();
            if ($(this).is(':checked')) {
                codes.add(code);
            } else {
                codes.delete(code);
            }
            Summary();
        })

        function Summary() {
            $.ajax({
                url: "{{ route('carts.summary') }}",
                type: 'GET',
                data: {
                    ids: Array.from(ids),
                    codes: Array.from(codes)
                },
                success: (response) => {
                    if (response.success) {
                        costCourse.text(response?.data.cost);
                        costDiscount.text(response?.data.discount);
                        totalCourse.text(response?.data.total);
                        $('.coupon').empty();
                        $('.list-coupons').empty();

                        if (response.data?.code) {
                            $('.coupon').append(`(${response.data?.code})`);
                        }
                        codes = new Set(response.data.codes);
                        if (response.data?.coupons?.length !== 0) {
                            $.each(response.data?.coupons, (index, value) => {
                                let isChecked = response.data?.codes.includes(value.code);
                                $('.list-coupons').append(boxCoupons(value.code, isChecked));
                            })
                        }
                    } else {}

                    response.message && Toast({
                        message: response.message,
                        type: response.type
                    });
                },
                error: () => {}
            });
        }

        _document.on('click', '#btn-apply', function() {
            let code = $('#code').val();
            if (code.trim() === '') return false;
            $('#code').val('');
            codes.add(code);
            Summary();
        });

        _document.on('click', '.btn-remove', function() {
            let _this = $(this).closest('tr');
            let id = _this.data('id');
            $.ajax({
                url: "{{ route('carts.remove') }}",
                type: 'POST',
                data: {
                    id
                },
                success: (response) => {
                    if (response.success) {
                        if (response.data) {
                            _this.remove();
                            updateCourseChecked();
                        } else {
                            loadData();
                        }
                        Toast({
                            message: response.message,
                            type: response.type
                        });
                    }
                },
                error: (xhr, status, error) => {
                    console.error(`message: ${error}`);
                }
            });
        });

        _document.on('click', '#btnCheckout', function() {
            //updateCodeChecked();
            $.ajax({
                url: "{{ route('carts.checkout') }}",
                type: 'POST',
                data: {
                    ids: Array.from(ids),
                    codes: Array.from(codes)
                },
                success: (response) => {
                    if (response.success) {
                        window.location.href = response?.data?.link ?? '';
                    } else {
                        response.message && Toast({
                            message: response.message,
                            type: response.type
                        });
                    }
                }

            });
        });

        //checkbox select all or cancel
        _document.on('click', 'input.select-courses', function() {
            let checked = this.checked;
            inputSelectCourse.each(function(index, item) {
                item.checked = checked;
            });
            updateCourseChecked();
        });
        //check selected items
        _document.on('click', 'input.select-course', function() {
            let selected = $('input.select-course:checked');
            inputSelectCourses[0].checked = selected.length === inputSelectCourse.length;
            updateCourseChecked();
        });

        //Render UI
        function boxItem(id, thumbnail, name, author, cost) {
            return `<tr data-id="${id}">
                        <th scope="row">
                            <input type="checkbox" class="form-check-input select-course"
                                name="select-course"/>
                        </th>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="${thumbnail}" alt=""
                                    class="pic-in-cart" />
                                <div class="ms-3">
                                    <p class="fw-bold mb-1">${name}</p>
                                    <p class="text-muted mb-0">${author}</p>
                                </div>
                            </div>
                        </td>
                        <td>${cost}</td>
                        <td>
                            <button type="button" data-mdb-button-init data-mdb-ripple-init
                                class="btn btn-danger btn-sm btn-rounded btn-remove">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>`;
        }

        function boxCart(item) {
            return `<div class="col-xxl-8 col-xl-12">
                        <div class="card mb-4">
                            <div class="card-header py-3">
                                <h5 class="mb-0">Giỏ Hàng</h5>
                            </div>
                            <div class="card-body">
                                <table class="table align-middle mb-0 bg-white text-center table-responsive">
                                    <thead class="bg-light">
                                        <tr>
                                            <th scope="col">
                                                <input type="checkbox" class="form-check-input select-courses" id='select-courses' name="select-courses" />
                                                <label for='select-courses'>Tất cả</label>
                                            </th>
                                            <th scope="col">Tên khóa học</th>
                                            <th scope="col">Giá</th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${item}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>`;
        }

        function boxSummary() {
            return `<div class="col-xxl-4 col-xl-12">
                        <div class="card mb-4">
                            <div class="card-header py-3">
                                <h5 class="mb-0">Tóm tắt đơn hàng</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 pb-0">
                                        Khóa học
                                        <span id='costCourse'>0 đ</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span>Mã ưu đãi <span class='coupon'></span></span>
                                        <span id='costDiscount'>0 đ</span>
                                    </li>
                                    <li class="list-group-item px-0">
                                        <div class="input-group mb-3">
                                            <input type="text" id="code" class="form-control"
                                                placeholder="Mã giảm giá" />
                                            <button class="btn btn-outline-secondary" type="button" data-mdb-ripple-init
                                                data-mdb-ripple-color="dark" id="btn-apply">
                                                Áp dụng
                                            </button>
                                        </div>
                                        <ul class="list-coupons">
                                        </ul>
                                    </li>
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 mb-3">
                                        <div>
                                            <strong>Tổng giá trị đơn hàng</strong>
                                        </div>
                                        <span><strong id='totalCourse'>0 đ</strong></span>
                                    </li>
                                </ul>
                                <button type="button" data-mdb-button-init data-mdb-ripple-init
                                    id='btnCheckout'
                                    class="btn btn-primary btn-lg btn-block">
                                    THANH TOÁN
                                </button>
                            </div>
                        </div>
                    </div>`;
        }


        function boxCoupons(code, isChecked = false) {
            return (`
                    <div class="form-check">
                      <input class="form-check-input select-code" type="checkbox" id="${code}" value="${code}" ${isChecked? 'checked': ''}/>
                      <label class="form-check-label" for="${code}"> ${code} </label>
                    </div>`);
        }

        function boxEmpty() {
            return (`<div class='text-center text-uppercase'>
                <p>Giỏ Hàng Trống</p>
                <p><a href='/'>Trang Chủ</a></p>
                </div>`);
        }
    </script>
@endsection
