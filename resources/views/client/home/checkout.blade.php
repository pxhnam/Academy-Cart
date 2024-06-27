@extends('client.main')

@section('title', 'Thanh Toán')

@section('styles')
    <style>
    </style>
@endsection

@section('main')

    <div class="row">
        <div class="col-md-12">
            <button class="btn-back">
                <i class="fa-solid fa-arrow-left"></i>
                &nbsp;Quay lại
            </button>
        </div>
        <div class="col-md-3 my-3"></div>
        <div class="col-md-6 my-3">
            <ul class="progressbar">
                <li class="active">Phương thức thanh toán</li>
                <li>Hoàn tất mua hàng</li>
            </ul>
        </div>
        <div class="col-md-3 my-3"></div>
        <div class="col-xl-7 col-md-12">
            <div class="list-course d-flex flex-column gap-2 bg-light box-shadow rounded p-3 mb-3">
                @foreach ($carts ?? [] as $cart)
                    <div
                        class="d-flex gap-3 flex-column flex-md-row align-items-center justify-content-center justify-content-md-start">
                        <img class="rounded" height="100px" width="200px" src="{{ $cart['thumbnail'] }}" alt="" />
                        <div class="d-flex flex-column flex-grow-1">
                            <h3 class="fw-bold fs-5">{{ $cart['course_name'] }}</h3>
                            <p class="mb-1"><i class="fa-regular fa-clock"></i> {{ $cart['duration'] }} giờ</p>
                            <p class="mb-0">Bởi {{ $cart['lecturer'] }}</p>
                        </div>
                        <div class="d-flex align-items-center">
                            <p class="fw-semibold m-0">{{ $cart['cost'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="box-summary bg-light box-shadow rounded p-3 mb-3">
                <div class="d-flex justify-content-between mb-3">
                    <span>Giá niêm yết:</span><span>{{ $base ?? '0 đ' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Giảm giá:</span><span>{{ $reduce ?? '0 đ' }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Mã ưu đãi:</span><span>{{ $discount ?? '0 đ' }}</span>
                </div>
                <hr />
                <div class="fw-bold d-flex justify-content-between">
                    <span>Tổng tiền:</span><span>{{ $total ?? '0 đ' }}</span>
                </div>
            </div>
        </div>
        <div class="col-xl-5 col-md-12">
            <div class="bg-light box-shadow p-3 mb-3 rounded">
                <h3>Phương thức thanh toán</h3>
                <div class="row d-flex flex-column gap-2 align-items-center" id="list-method">
                    @if ($errors->has('method'))
                        <small class="text-danger fst-italic">
                            (*) {{ $errors->first('method') }}
                        </small>
                    @endif
                    @foreach ($paymentMethods as $method)
                        <div class="col-md-6 box-shadow p-3 method-item" data-id="{{ $method }}">
                            @if ($method == 'MOMO')
                                <img src="./assets/icons/momo.svg" alt="{{ $method }}" class="box-shadow rounded" />
                            @elseif ($method == 'VNPAY')
                                <img src="./assets/icons/vnpay.svg" alt="{{ $method }}"
                                    class="box-shadow p-2 rounded" />
                            @endif
                            Thanh toán qua {{ $method }}
                        </div>
                    @endforeach
                </div>
            </div>
            <form action="" method="POST">
                <div class="col-md-12 d-flex justify-content-center">
                    @csrf
                    <input type="hidden" value="" id="method" name="method" />
                    <button class="w-50 btn btn-info text-white my-3 py-2 fw-semibold text-uppercase" id="btn-payment"
                        type="submit">
                        Thanh Toán
                    </button>
                </div>
            </form>
        </div>
        <div class="col-md-12 p-3">
            <p class="notice-checkout">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                Sau khi chuyển khoản quý khách vui lòng chụp lại màn hình thanh
                toán thành công hoặc biên lai chuyển tiền. Trendemy sẽ liên hệ với
                quý khách ngay khi nhận được khoản tiền thanh toán thông qua thông
                tin email, số điện thoại được đăng ký.
            </p>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        _document.ready(function() {
            $('#list-method .method-item').on('click', function() {
                $('#list-method .method-item').removeClass('active');
                $(this).addClass('active');
                $('#method').val($(this).data('id'));
            });
        });
        _document.on('click', '.btn-back', function() {
            window.location.href = '/gio-hang';
        });
        $('#btn-payment').click(function(e) {
            let method = $('#method').val();
            if (method == 'BANK') { //|| method == 'MOMO'
                e.preventDefault();
                $.ajax({
                    url: '/thanh-toan',
                    type: 'POST',
                    data: {
                        method
                    },
                    success: (response) => {
                        if (response.success) {
                            openModal({
                                title: "Thanh toán hóa đơn",
                                body: `<div class="text-center">
                                        <img src="${response.qrPay}" alt="">
                                        <p class='mt-3'>Thanh toán thành công. Vui lòng nhấn xác nhận để hệ thống kiểm tra.</p>
                                       </div>`,
                                ok: "Xác nhận",
                                cancel: "Hủy",
                            });
                        }
                    },
                    error: () => {}
                })
            }
        });
    </script>
@endsection
