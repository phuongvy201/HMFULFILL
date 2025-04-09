<aside
    x-data="{ selected: 'Dashboard' }"
    :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
    class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0">
    <!-- SIDEBAR HEADER -->
    <div
        :class="sidebarToggle ? 'justify-center' : 'justify-between'"
        class="flex items-center gap-2 ">
        <a href="index.html">
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
        <nav x-data="{ selected: $persist('Dashboard') }">
            <!-- Menu Group -->
            <div>
                <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400">
                    <span
                        class="menu-group-title"
                        :class="sidebarToggle ? 'lg:hidden' : ''">
                        MENU
                    </span>

                    <svg
                        :class="sidebarToggle ? 'lg:block hidden' : 'hidden'"
                        class="mx-auto fill-current menu-group-icon"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                            fill="" />
                    </svg>
                </h3>

                <ul class="flex flex-col gap-4 mb-6">
                    <!-- Menu Item Dashboard -->
                    <li>
                        <a
                            href="#"
                            @click.prevent="selected = (selected === 'Dashboard' ? '' : 'Dashboard')"
                            class="menu-item group"
                            :class="(selected === 'Dashboard') || (page === 'ecommerce' || page === 'analytics' || page === 'marketing' || page === 'crm' || page === 'stocks') ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg
                                :class="(selected === 'Dashboard') || (page === 'ecommerce' || page === 'analytics' || page === 'marketing' || page === 'crm' || page === 'stocks') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
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
                    <li>
                        <a
                            href="calendar.html"
                            @click="selected = (selected === 'Calendar' ? '':'Calendar')"
                            class="menu-item group"
                            :class=" (selected === 'Calendar') && (page === 'calendar') ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg
                                :class="(selected === 'Calendar') && (page === 'calendar') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'"
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
                    </li>
                    <!-- Menu Item Calendar -->



                    <!-- Menu Item Forms -->
                    <li>
                        <a
                            href="/admin/products"
                            @click.prevent="selected = (selected === 'Products' ? '' : 'Products')"
                            class="menu-item group"
                            :class="(selected === 'Products') || (page === 'formElements' || page === 'formLayout' || page === 'proFormElements' || page === 'proFormLayout') ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg
                                :class="(selected === 'Products') || (page === 'formElements' || page === 'formLayout' || page === 'proFormElements' || page === 'proFormLayout') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H18.5001C19.7427 20.75 20.7501 19.7426 20.7501 18.5V5.5C20.7501 4.25736 19.7427 3.25 18.5001 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H18.5001C18.9143 4.75 19.2501 5.08579 19.2501 5.5V18.5C19.2501 18.9142 18.9143 19.25 18.5001 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V5.5ZM6.25005 9.7143C6.25005 9.30008 6.58583 8.9643 7.00005 8.9643L17 8.96429C17.4143 8.96429 17.75 9.30008 17.75 9.71429C17.75 10.1285 17.4143 10.4643 17 10.4643L7.00005 10.4643C6.58583 10.4643 6.25005 10.1285 6.25005 9.7143ZM6.25005 14.2857C6.25005 13.8715 6.58583 13.5357 7.00005 13.5357H17C17.4143 13.5357 17.75 13.8715 17.75 14.2857C17.75 14.6999 17.4143 15.0357 17 15.0357H7.00005C6.58583 15.0357 6.25005 14.6999 6.25005 14.2857Z"
                                    fill="" />
                            </svg>

                            <span
                                class="menu-item-text"
                                :class="sidebarToggle ? 'lg:hidden' : ''">
                                Products
                            </span>

                            <svg
                                class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                :class="[(selected === 'Products') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
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
                            :class="(selected === 'Products') ? 'block' :'hidden'">
                            <ul
                                :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                <li>
                                    <a
                                        href="/admin/products"
                                        class="menu-dropdown-item group"
                                        :class="page === 'formElements' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        Product List
                                    </a>
                                </li>
                            </ul>
                            <ul
                                :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                <li>
                                    <a
                                        href="{{ route('admin.products.create') }}"
                                        class="menu-dropdown-item group"
                                        :class="page === 'formElements' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        Add Product
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!-- Dropdown Menu End -->
                    </li>
                    <!-- Menu Item Forms -->
                    <li>
                        <a
                            href="/admin/categories"
                            @click.prevent="selected = (selected === 'Categories' ? '' : 'Categories')"
                            class="menu-item group"
                            :class="(selected === 'Categories') || (page === 'formElements' || page === 'formLayout' || page === 'proFormElements' || page === 'proFormLayout') ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg
                                :class="(selected === 'Categories') || (page === 'formElements' || page === 'formLayout' || page === 'proFormElements' || page === 'proFormLayout') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M3 3h18v2H3V3zm0 4h18v2H3V7zm0 4h18v2H3v-2zm0 4h18v2H3v-2zm0 4h18v2H3v-2z"
                                    fill="currentColor" />
                            </svg>

                            <span
                                class="menu-item-text"
                                :class="sidebarToggle ? 'lg:hidden' : ''">
                                Categories
                            </span>

                            <svg
                                class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                :class="[(selected === 'Categories') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
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
                            :class="(selected === 'Categories') ? 'block' :'hidden'">
                            <ul
                                :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                <li>
                                    <a
                                        href="/admin/categories"
                                        class="menu-dropdown-item group"
                                        :class="page === 'formElements' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        Category List
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!-- Dropdown Menu End -->
                    </li>
                    <!-- Menu Item Pages -->
                    <li>
                        <a
                            href="/admin/orders"
                            @click.prevent="selected = (selected === 'Orders' ? '' : 'Orders')"
                            class="menu-item group"
                            :class="(selected === 'Orders') || (page === 'orderList') ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg
                                :class="(selected === 'Orders') || (page === 'orderList') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M7 2C5.34315 2 4 3.34315 4 5V6H3C2.44772 6 2 6.44772 2 7V8C2 8.55228 2.44772 9 3 9H4V20C4 21.6569 5.34315 23 7 23H17C18.6569 23 20 21.6569 20 20V9H21C21.5523 9 22 8.55228 22 8V7C22 6.44772 21.5523 6 21 6H20V5C20 3.34315 18.6569 2 17 2H7ZM7 4H17C17.5523 4 18 4.44772 18 5V6H6V5C6 4.44772 6.44772 4 7 4ZM4 11H20V20C20 20.5523 19.5523 21 19 21H5C4.44772 21 4 20.5523 4 20V11ZM6 12H18V18H6V12Z"
                                    fill="currentColor" />
                            </svg>

                            <span
                                class="menu-item-text"
                                :class="sidebarToggle ? 'lg:hidden' : ''">
                                Orders
                            </span>

                            <svg
                                class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
                                :class="[(selected === 'Orders') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
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
                            :class="(selected === 'Orders') ? 'block' :'hidden'">
                            <ul
                                :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                <li>
                                    <a
                                        href="{{ route('admin.order-fulfillment-list') }}"
                                        class="menu-dropdown-item group"
                                        :class="page === 'orderList' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        Order List
                                    </a>
                                </li>
                            </ul>
                            <ul
                                :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
                                <li>
                                    <a
                                        href="{{ route('admin.submitted-orders') }}"
                                        class="menu-dropdown-item group"
                                        :class="page === 'orderCreate' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                                        Submitted Orders
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!-- Dropdown Menu End -->
                    </li>
                </ul>
            </div>

            <!-- Others Group -->

        </nav>
        <!-- Sidebar Menu -->

        <!-- Promo Box -->

        <!-- Promo Box -->
    </div>
</aside>