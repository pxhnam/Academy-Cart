@extends('client.main')

@section('title', 'Thanh Toán')

@section('styles')
    <style>
        .pic-in-cart {

            width: 120px;
            height: 70px;
        }
    </style>
@endsection

@section('main')
    <form action="" method="POST">
        @csrf
        <section class="h-100 gradient-custom">
            <div class="container py-5">
                <div class="row d-flex justify-content-center my-4">
                    <div class="col-xxl-8 col-xl-12">
                        <div class="card mb-4">
                            <div class="card-header py-3">
                                <h5 class="mb-0">Thông Tin Thanh Toán</h5>
                            </div>
                            <div class="card-body">
                                <div class="payment-methods">
                                    <h6>Phương Thức Thanh Toán</h6>
                                    @if ($errors->has('method'))
                                        <small class="text-danger fst-italic">
                                            (*) {{ $errors->first('method') }}
                                        </small>
                                    @endif
                                    @foreach ($paymentMethods as $method)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="method"
                                                id="{{ $method }}" value="{{ $method }}" />
                                            <label class="form-check-label" for="{{ $method }}">
                                                Thanh toán qua {{ $method }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <table class="table align-middle mb-0 bg-white">
                                    <thead class="bg-light">
                                        <tr>
                                            <th scope="col">Tên khóa học</th>
                                            <th scope="col">Giá</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($carts ?? [] as $cart)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ $cart['thumbnail'] }}" alt=""
                                                            class="pic-in-cart" />
                                                        <div class="ms-3">
                                                            <p class="fw-bold mb-1">{{ $cart['course_name'] }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                {{-- $cart['cost'] --}}
                                                <td>{{ $cart['cost'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-4 col-xl-12">
                        <div class="card mb-4">
                            <div class="card-header py-3">
                                <h5 class="mb-0">Tóm tắt đơn hàng</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 pb-0">
                                        Khóa học
                                        <span>{{ $cost ?? '0 đ' }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        Giảm giá
                                        <span>{{ $discount ?? '0 đ' }}</span>
                                    </li>
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 mb-3">
                                        <div>
                                            <strong>Tổng giá trị đơn hàng</strong>
                                        </div>
                                        <span><strong>{{ $total ?? '0 đ' }}</strong></span>
                                    </li>
                                </ul>
                                <button type="submit" data-mdb-ripple-init class="btn btn-primary btn-lg btn-block">
                                    THANH TOÁN
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </form>
@endsection

@section('scripts')

@endsection
