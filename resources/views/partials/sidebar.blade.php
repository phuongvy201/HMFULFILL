<aside
    x-data="{ selectedMenu: $persist('Dashboard'), page: '{{ $page ?? "Dashboard" }}' }"
    :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
    class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0">
    <!-- SIDEBAR HEADER -->
    <div :class="sidebarToggle ? 'justify-center' : 'justify-between'" class="flex items-center gap-2 ">
        <a href="/">
            <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
                <img class="dark:hidden" src="{{ asset('assets/images/logo HM-02.png') }}" alt="Logo" />
                <img class="hidden dark:block" src="{{ asset('assets/images/logo HM-02.png') }}" alt="Logo" />
            </span>
            <img class="logo-icon" :class="sidebarToggle ? 'lg:block' : 'hidden'" src="{{ asset('assets/images/logo HM-02.png') }}" alt="Logo" />
        </a>
    </div>
    <!-- SIDEBAR HEADER -->
    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
        <nav x-data="{ selectedMenu: $persist('Dashboard') }">
            <div>
                <ul class="flex flex-col gap-4 mb-6">
                    <!-- ADMIN MENUS -->
                    @if(Auth::check() && Auth::user()->role === 'admin')
                    @include('partials.admin.sidebar-menus')
                    @endif

                    <!-- CUSTOMER MENUS -->
                    @if(Auth::check() && Auth::user()->role === 'customer')
                    @include('partials.customer.sidebar-menus')
                    @endif

                    <!-- DESIGNER MENUS -->
                    @if(Auth::check() && Auth::user()->role === 'design')
                    @include('partials.designer.sidebar-menus')
                    @endif

                    <!-- LOGOUT -->
                    <li>
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            class="menu-item group"
                            :class="page === 'logout' ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg :class="page === 'logout' ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M16 17L21 12L16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span :class="sidebarToggle ? 'lg:hidden' : ''">Sign Out</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</aside>