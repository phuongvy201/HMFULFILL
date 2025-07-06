<aside
    x-data="{ selectedCustomer: $persist('Dashboard'), pageCustomer: '{{ $page ?? "Dashboard" }}' }"
    :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
    class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0">
    <!-- SIDEBAR HEADER -->
    <div
        :class="sidebarToggle ? 'justify-center' : 'justify-between'"
        class="flex items-center gap-2 ">
        <a href="{{ route('customer.index') }}">
            <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
                <img class="dark:hidden " src="{{ asset('assets/images/logo HM-02.png') }}" alt="Logo" />
                <img
                    class="hidden dark:block"
                    src="{{ asset('assets/images/logo HM-02.png') }}"
                    alt="Logo" />
            </span>

            <img
                class="logo-icon"
                :class="sidebarToggle ? 'lg:block' : 'hidden'"
                src="{{ asset('assets/images/logo HM-02.png') }}"
                alt="Logo" />
        </a>
    </div>
    <!-- SIDEBAR HEADER -->

    <div
        class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
        <!-- Sidebar Menu -->
        <nav x-data="{ selectedCustomer: $persist('Dashboard') }">
            <!-- Menu Group -->
            <div>

                <ul class="flex flex-col gap-4 mb-6">
                    <!-- Menu Item Dashboard -->
                    <li>
                        <a
                            href="{{ route('customer.dashboard') }}"
                            class="menu-item group"
                            :class="(selectedCustomer === 'Dashboard') || (pageCustomer === 'dashboard') ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg
                                :class="(selectedCustomer === 'Dashboard') || (pageCustomer === 'dashboard') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z"
                                    fill="" />
                            </svg>

                            <span
                                class="menu-item-text"
                                :class="sidebarToggle ? 'lg:hidden' : ''">
                                Dashboard
                            </span>
                        </a>

                        <!-- Dropdown Menu Start -->

                        <!-- Dropdown Menu End -->
                    </li>
                    <!-- Menu Item Dashboard -->

                    <!-- Menu Item Calendar -->
                    <!-- <li>
                        <a
                            href="calendar.html"
                            @click="selectedCustomer = (selectedCustomer === 'Calendar' ? '':'Calendar')"
                            class="menu-item group"
                            :class=" (selectedCustomer === 'Calendar') && (pageCustomer === 'calendar') ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg
                                :class="(selectedCustomer === 'Calendar') && (pageCustomer === 'calendar') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M8 2C8.41421 2 8.75 2.33579 8.75 2.75V3.75H15.25V2.75C15.25 2.33579 15.5858 2 16 2C16.4142 2 16.75 2.33579 16.75 2.75V3.75H18.5C19.7426 3.75 20.75 4.75736 20.75 6V9V19C20.75 20.2426 19.7426 21.25 18.5 21.25H5.5C4.25736 21.25 3.25 20.2426 3.25 19V9V6C3.25 4.75736 4.25736 3.75 5.5 3.75H7.25V2.75C7.25 2.33579 7.58579 2 8 2ZM8 5.25H5.5C5.08579 5.25 4.75 5.58579 4.75 6V8.25H19.25V6C19.25 5.58579 18.9142 5.25 18.5 5.25H16H8ZM19.25 9.75H4.75V19C4.75 19.4142 5.08579 19.75 5.5 19.75H18.5C18.9142 19.75 19.25 19.4142 19.25 19V9.75Z"
                                    fill="" />
                            </svg>

                            <span
                                class="menu-item-text"
                                :class="sidebarToggle ? 'lg:hidden' : ''">
                                Calendar
                            </span>
                        </a>
                    </li> -->
                    <!-- Menu Item Calendar -->

                    <!-- Menu Item Finance -->
                    <li>
                        <a
                            href="#"
                            @click.prevent="selectedCustomer = (selectedCustomer === 'Finance' ? '' : 'Finance')"
                            class="menu-item group"
                            :class="(selectedCustomer === 'Finance') || (pageCustomer === 'wallet' || pageCustomer === 'topupRequests') ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg
                                :class="(selectedCustomer === 'Finance') || (pageCustomer === 'wallet' || pageCustomer === 'topupRequests') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M3 3H21C21.55 3 22 3.45 22 4V20C22 20.55 21.55 21 21 21H3C2.45 21 2 20.55 2 20V4C2 3.45 2.45 3 3 3ZM3 5V20H21V5H3ZM6 8H18V10H6V8ZM6 12H18V14H6V12ZM6 16H12V18H6V16Z"
                                    fill="" />
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                Finance
                            </span>
                            <svg
                                class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                :class="[(selectedCustomer === 'Finance') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
                                width="20"
                                height="20"
                                viewBox="0 0 20 20"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585"
                                    stroke=""
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </a>

                        <!-- Dropdown Menu Start -->
                        <div
                            class="overflow-hidden transform translate"
                            :class="(selectedCustomer === 'Finance') ? 'block' :'hidden'">
                            <ul
                                :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                <li>
                                    <a
                                        href="{{ route('customer.wallet') }}"
                                        class="menu-dropdown-item group"
                                        :class="pageCustomer === 'wallet' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        Wallet
                                    </a>
                                </li>

                            </ul>
                        </div>
                        <!-- Dropdown Menu End -->
                    </li>
                    <!-- Menu Item Finance -->

                    <!-- Menu Item Tier -->
                    <li>
                        <a
                            href="{{ route('customer.tier') }}"
                            @click="selectedCustomer = (selectedCustomer === 'Tier' ? '' : 'Tier')"
                            class="menu-item group"
                            :class="pageCustomer === 'tier' ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg
                                :class="pageCustomer === 'tier' ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M12 2L15.09 8.26L22 9L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9L8.91 8.26L12 2Z"
                                    fill="" />
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                My Tier
                            </span>
                        </a>
                    </li>
                    <!-- Menu Item Tier -->

                    <!-- Menu Item Orders -->
                    <li>
                        <a
                            href="#"
                            @click.prevent="selectedCustomer = (selectedCustomer === 'Orders' ? '' : 'Orders')"
                            class="menu-item group"
                            :class="(selectedCustomer === 'Orders') || (pageCustomer === 'orderList') ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg
                                :class="(selectedCustomer === 'Orders') || (pageCustomer === 'orderList') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M7 2C5.34315 2 4 3.34315 4 5V6H3C2.44772 6 2 6.44772 2 7V8C2 8.55228 2.44772 9 3 9H4V20C4 21.6569 5.34315 23 7 23H17C18.6569 23 20 21.6569 20 20V9H21C21.5523 9 22 8.55228 22 8V7C22 6.44772 21.5523 6 21 6H20V5C20 3.34315 18.6569 2 17 2H7ZM7 4H17C17.5523 4 18 4.44772 18 5V6H6V5C6 4.44772 6.44772 4 7 4ZM4 11H20V20C20 20.5523 19.5523 21 19 21H5C4.44772 21 4 20.5523 4 20V11ZM6 12H18V18H6V12Z"
                                    fill="" />
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                Orders
                            </span>
                            <svg
                                class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                :class="[(selectedCustomer === 'Orders') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
                                width="20"
                                height="20"
                                viewBox="0 0 20 20"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585"
                                    stroke=""
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </a>
                        <div
                            class="overflow-hidden transform translate"
                            :class="(selectedCustomer === 'Orders') ? 'block' :'hidden'">
                            <ul
                                :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                <li>
                                    <a
                                        href="{{ route('customer.order-list') }}"
                                        class="menu-dropdown-item group"
                                        :class="pageCustomer === 'orderList' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        Order Import File
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="{{ route('customer.order-customer') }}"
                                        class="menu-dropdown-item group"
                                        :class="pageCustomer === 'importLogs' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        Uploaded Order
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>
                    <!-- Menu Item Orders -->

                    <!-- Menu Item API Token -->
                    <li>
                        <a
                            href="{{ route('api-token.show') }}"
                            @click="selectedCustomer = (selectedCustomer === 'APIToken' ? '' : 'APIToken')"
                            class="menu-item group"
                            :class="pageCustomer === 'apiToken' ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg
                                :class="pageCustomer === 'apiToken' ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 5C13.66 5 15 6.34 15 8C15 9.66 13.66 11 12 11C10.34 11 9 9.66 9 8C9 6.34 10.34 5 12 5ZM12 19.2C9.5 19.2 7.29 17.92 6 15.98C6.03 13.99 10 12.9 12 12.9C13.99 12.9 17.97 13.99 18 15.98C16.71 17.92 14.5 19.2 12 19.2Z"
                                    fill="currentColor" />
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                                API Token
                            </span>
                        </a>
                    </li>
                    <!-- Menu Item API Token -->

                    <!-- Menu Item Logout -->
                    <li>
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            class="menu-item group"
                            :class="pageCustomer === 'logout' ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg :class="pageCustomer === 'logout' ? 'menu-item-icon-active' : 'menu-item-icon-inactive'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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
                    <!-- Menu Item Logout -->

                </ul>
            </div>

            <!-- Others Group -->

        </nav>
        <!-- Sidebar Menu -->

        <!-- Promo Box -->

        <!-- Promo Box -->
    </div>
</aside>