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
        var ids = [];
        _document.ready(function() {
            loadData();
        })

        function loadData() {
            $.ajax({
                url: "{{ route('carts.list') }}",
                type: 'GET',
                success: (response) => {
                    row.empty();
                    if (response.success) {
                        let courses = response.data;
                        let data = '';
                        $.each(courses, (index, course) =>
                            data += boxItem(
                                course.id,
                                course.thumbnail,
                                course.name,
                                course.lecturer,
                                course.cost
                            )
                        )
                        row.append(boxCart(data));
                        row.append(boxSummary());
                        Summary();
                    } else {
                        row.append(boxEmpty());
                    }
                }
            })
        }

        function loopCourse() {
            ids = [];
            $('input.select-item').each(function() {
                let id = $(this).closest('tr').data('id');
                if ($(this).is(':checked')) {
                    ids.push(id);
                }
            });
            Summary();
        }

        function Summary(code = '') {
            $.ajax({
                url: "{{ route('carts.summary') }}",
                type: 'GET',
                data: {
                    ids,
                    code
                },
                success: (response) => {
                    if (response.success) {
                        $('#costCourse').text(response?.data.cost);
                        $('#costDiscount').text(response?.data.discount);
                        $('#totalCourse').text(response?.data.total);
                        $('.coupon').empty();
                        $('.list-coupons').empty();

                        if (response.data?.code) {
                            $('.coupon').append(`(${response.data?.code})`);
                        }
                        if (response.data?.coupons?.length !== 0) {
                            $.each(response.data?.coupons, (index, value) => {
                                $('.list-coupons').append(
                                    boxCoupons(value.code, response.data?.code === value.code)
                                );
                            })
                        }
                    } else {
                        response.message && Toast({
                            message: response.message,
                            type: response.type
                        });
                    }
                },
                error: () => {}
            });
        }

        _document.on('change', 'input[name="coupons"]', function() {
            if ($(this).is(':checked')) {
                Summary($(this).val());
            }
        });
        _document.on('click', '#btn-apply', function() {
            let code = $('#code').val();
            if (code.trim() === '') return false;
            $('#code').val('');
            Summary(code);
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
                            loopCourse();
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
            $.ajax({
                url: "{{ route('carts.checkout') }}",
                type: 'POST',
                data: {
                    ids,
                    code: $('input[name="coupons"]:checked').val()
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

        //checkbox
        _document.on('change', 'input[type="checkbox"]', function() {
            let len = $('input.select-item:checked').length;
            let btnCheckout = $('#btnCheckout');
            let btnApply = $('#btn-apply');
            if (len === 0) {
                btnCheckout.prop('disabled', true);
                btnApply.prop('disabled', true);
            } else {
                btnCheckout.prop('disabled', false);
                btnApply.prop('disabled', false);
            }
            loopCourse();
        });

        //checkbox select all or cancel
        _document.on('click', 'input.select-all', function() {
            var checked = this.checked;
            $('input.select-item').each(function(index, item) {
                item.checked = checked;
            });
        });
        //check selected items
        _document.on('click', 'input.select-item', function() {
            var checked = this.checked;
            var all = $('input.select-all')[0];
            var total = $('input.select-item').length;
            var len = $('input.select-item:checked').length;
            all.checked = len === total;
        });

        function boxItem(id, thumbnail, name, author, cost) {
            return `<tr data-id="${id}">
                        <th scope="row">
                            <input type="checkbox" class="form-check-input select-item"
                                name="select-item"/>
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
                                                <input type="checkbox" class="form-check-input select-all" id='select-all' name="select-all" />
                                                <label for='select-all'>Tất cả</label>
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
                                                data-mdb-ripple-color="dark" id="btn-apply" disabled>
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
                                    disabled
                                    id='btnCheckout'
                                    class="btn btn-primary btn-lg btn-block">
                                    THANH TOÁN
                                </button>
                            </div>
                        </div>
                    </div>`;
        }

        function boxCoupons(code, check = false) {
            return (`
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="coupons" id="${code}" value="${code}" ${check? 'checked': ''}/>
                      <label class="form-check-label" for="${code}"> ${code} </label>
                    </div>`);
        }

        function boxEmpty() {
            return (`<div class='text-center text-uppercase'>Giỏ Hàng Trống</div>`);
        }
    </script>
@endsection
