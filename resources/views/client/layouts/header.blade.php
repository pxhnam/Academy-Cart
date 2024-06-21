<!-- Navbar -->
{{-- <nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary fixed-top">
    <!-- Container wrapper -->
    <div class="container-fluid">
        <!-- Toggle button -->
        <button data-mdb-collapse-init class="navbar-toggler" type="button" data-mdb-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Collapsible wrapper -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Navbar brand -->
            <a class="navbar-brand mt-2 mt-lg-0" href="/">
                Academy
            </a>
            <!-- Left links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Team</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Projects</a>
                </li>
            </ul>
            <!-- Left links -->
        </div>
        <!-- Collapsible wrapper -->

        <!-- Right elements -->
        <div class="d-flex align-items-center">
            <!-- Icon -->
            <a class="text-reset me-3" href="{{ route('cart') }}">
                <i class="fas fa-shopping-cart"></i>
            </a>
            @auth
                <div class="dropdown">
                    <a data-mdb-dropdown-init class="dropdown-toggle d-flex align-items-center text-dark" href="#"
                        id="navbarDropdownMenuAvatar" role="button" aria-expanded="false">
                        Xin chào, {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuAvatar">
                        <li>
                            <a class="dropdown-item" href="#">My profile</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">Settings</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}">Logout</a>
                        </li>
                    </ul>
                </div>
            @else
                <a href="{{ route('login') }}" data-mdb-ripple-init type="button" class="btn btn-link px-3 me-2">
                    Login
                </a>
                <a href="{{ route('register') }}" data-mdb-ripple-init type="button" class="btn btn-primary me-3">
                    Sign up for free
                </a>
            @endauth

        </div>
        <!-- Right elements -->
    </div>
    <!-- Container wrapper -->
</nav> --}}
<!-- Navbar -->
{{-- fixed-top --}}
{{-- <li class="nav-item">
    <a class="nav-link{{ request()->routeIs('client.home') ? ' active' : '' }}"
        href="{{ route('client.home') }}">
        Trang Chủ
    </a>
</li>
<li class="nav-item">
    <a class="nav-link{{ request()->routeIs('client.product') ? ' active' : '' }}"
        href="{{ route('client.product') }}">
        Sản Phẩm
    </a>
</li> --}}
{{--
    <!-- Notifications -->
            <div class="dropdown">
                <a data-mdb-dropdown-init class="text-reset me-3 dropdown-toggle hidden-arrow" href="#"
                    id="navbarDropdownMenuLink" role="button" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                    <span class="badge rounded-pill badge-notification bg-danger">1</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                    <li>
                        <a class="dropdown-item" href="#">Some news</a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">Another news</a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">Something else here</a>
                    </li>
                </ul>
            </div>
            <!-- Avatar -->
            <div class="dropdown">
                <a data-mdb-dropdown-init class="dropdown-toggle d-flex align-items-center hidden-arrow" href="#"
                    id="navbarDropdownMenuAvatar" role="button" aria-expanded="false">
                    <img src="https://mdbcdn.b-cdn.net/img/new/avatars/2.webp" class="rounded-circle" height="25"
                        alt="Black and White Portrait of a Man" loading="lazy" />
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuAvatar">
                    <li>
                        <a class="dropdown-item" href="#">My profile</a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">Settings</a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">Logout</a>
                    </li>
                </ul>
            </div>
    --}}
{{--
    <img src="https://mdbcdn.b-cdn.net/img/new/avatars/2.webp" class="rounded-circle" height="25"
                            alt="Black and White Portrait of a Man" loading="lazy" />
    --}}


<nav class="navbar navbar-expand-lg bg-body-tertiary px-3">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">
            <img src="{{ asset('assets/icons/logo.svg') }}" alt="" />
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav box-search-nav m-auto my-3 my-lg-0">
                <div class="form-search d-none d-sm-flex">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Input something..." />
                </div>
                <button class="btn btn-outline-light d-flex d-sm-none">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </ul>
            @auth
                <ul class="navbar-nav menu-user-nav gap-3">
                    <li class="nav-item">
                        <a href="#" class="btn-my-courses">Khóa học của tôi</a>
                    </li>
                    <li class="nav-item">
                        <button class="btn-icon" id="btn-cart">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="btn-icon">
                            <i class="fa-solid fa-bell"></i>
                        </button>
                    </li>
                    <li class="nav-item dropdown fw-semibold">
                        <div class="box-avatar-nav">
                            <img class="avatar" src="" alt="" />
                        </div>

                        <ul class="menu-user">
                            <li><a class="menu-item" href="#">Đổi Mật Khẩu</a></li>
                            <li><a class="menu-item" href="#">Đơn hàng</a></li>
                            <li><a class="menu-item" href="{{ route('logout') }}">Đăng Xuất</a></li>
                        </ul>
                    </li>
                </ul>
            @else
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Đăng Nhập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">Đăng Ký</a>
                    </li>
                </ul>
            @endauth
        </div>
    </div>
</nav>
