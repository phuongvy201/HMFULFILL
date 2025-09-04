<!-- ADMIN MENUS -->
<!-- Menu Item Dashboard -->
<li>
    <a
        href="#"
        @click.prevent="selectedMenu = (selectedMenu === 'Dashboard' ? '' : 'Dashboard')"
        class="menu-item group"
        :class="(selectedMenu === 'Dashboard') || (page === 'ecommerce' || page === 'analytics' || page === 'marketing' || page === 'crm' || page === 'stocks') ? 'menu-item-active' : 'menu-item-inactive'">
        <svg
            :class="(selectedMenu === 'Dashboard') || (page === 'ecommerce' || page === 'analytics' || page === 'marketing' || page === 'crm' || page === 'stocks') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
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
        <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
            Dashboard
        </span>
        <svg
            class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
            :class="[(selectedMenu === 'Dashboard') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
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
        :class="(selectedMenu === 'Dashboard') ? 'block' :'hidden'">
        <ul
            :class="sidebarToggle ? 'lg:hidden' : 'flex'"
            class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
            <li>
                <a
                    href="{{ route('admin.statistics.dashboard') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'dashboard' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                    Order Statistics
                </a>
            </li>
            <li>
                <a
                    href="{{ route('admin.statistics.topup-dashboard') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'topup-dashboard' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                    Topup Statistics
                </a>
            </li>
            <li>
                <a
                    href="{{ route('admin.statistics.tier-dashboard') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'tier-dashboard' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                    Tier Statistics
                </a>
            </li>
        </ul>
    </div>
</li>

<!-- Menu Item Finance -->
<li>
    <a
        href="#"
        @click.prevent="selectedMenu = (selectedMenu === 'Finance' ? '' : 'Finance')"
        class="menu-item group"
        :class="(selectedMenu === 'Finance') || (page === 'wallet' || page === 'topupRequests') ? 'menu-item-active' : 'menu-item-inactive'">
        <svg
            :class="(selectedMenu === 'Finance') || (page === 'wallet' || page === 'topupRequests') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
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
            :class="[(selectedMenu === 'Finance') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
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
        :class="(selectedMenu === 'Finance') ? 'block' :'hidden'">
        <ul
            :class="sidebarToggle ? 'lg:hidden' : 'flex'"
            class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
            <li>
                <a
                    href="{{ route('admin.finance.balance-overview') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'wallet' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                    Balance Overview
                </a>
            </li>
            <li>
                <a
                    href="{{ route('admin.topup.requests') }}"
                    class="menu-dropdown-item group"
                    :class="pageAdmin === 'topupRequests' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                    Topup Requests
                </a>
            </li>
        </ul>
    </div>
</li>

<!-- Menu Item Products -->
<li>
    <a
        href="/admin/products"
        @click.prevent="selectedMenu = (selectedMenu === 'Products' ? '' : 'Products')"
        class="menu-item group"
        :class="(selectedMenu === 'Products') || (page === 'formElements' || page === 'formLayout' || page === 'proFormElements' || page === 'proFormLayout') ? 'menu-item-active' : 'menu-item-inactive'">
        <svg
            :class="(selectedMenu === 'Products') || (page === 'formElements' || page === 'formLayout' || page === 'proFormElements' || page === 'proFormLayout') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H18.5001C19.7427 20.75 20.7501 19.7426 20.7501 18.5V5.5C20.7501 4.25736 19.7427 3.25 18.5001 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H18.5001C18.9143 4.75 19.2501 5.08579 19.2501 5.5V18.5C19.2501 18.9142 18.9143 19.25 18.5001 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V5.5ZM6.25005 9.7143C6.25005 9.30008 6.58583 8.9643 7.00005 8.9643L17 8.96429C17.4143 8.96429 17.75 9.30008 17.75 9.71429C17.75 10.1285 17.4143 10.4643 17 10.4643L7.00005 10.4643C6.58583 10.4643 6.25005 10.1285 6.25005 9.7143ZM6.25005 14.2857C6.25005 13.8715 6.58583 13.5357 7.00005 13.5357H17C17.4143 13.5357 17.75 13.8715 17.75 14.2857C17.75 14.6999 17.4143 15.0357 17 15.0357L7.00005 15.0357C6.58583 15.0357 6.25005 14.6999 6.25005 14.2857Z"
                fill="" />
        </svg>
        <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
            Products
        </span>
        <svg
            class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
            :class="[(selectedMenu === 'Products') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
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
        :class="(selectedMenu === 'Products') ? 'block' :'hidden'">
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
            <li>
                <a
                    href="{{ route('admin.products.create') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'formElements' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                    Add Product
                </a>
            </li>
            <li>
                <a
                    href="{{ route('admin.user-pricing.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'userPricing' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                    User Pricing
                </a>
            </li>
        </ul>
    </div>
</li>

<!-- Menu Item Categories -->
<li>
    <a
        href="/admin/categories"
        @click.prevent="selectedMenu = (selectedMenu === 'Categories' ? '' : 'Categories')"
        class="menu-item group"
        :class="(selectedMenu === 'Categories') || (page === 'formElements' || page === 'formLayout' || page === 'proFormElements' || page === 'proFormLayout') ? 'menu-item-active' : 'menu-item-inactive'">
        <svg
            :class="(selectedMenu === 'Categories') || (page === 'formElements' || page === 'formLayout' || page === 'proFormElements' || page === 'proFormLayout') ? 'menu-item-icon-active'  :'menu-item-icon-inactive'"
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
        <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
            Categories
        </span>
        <svg
            class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
            :class="[(selectedMenu === 'Categories') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
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
        :class="(selectedMenu === 'Categories') ? 'block' :'hidden'">
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
</li>

<!-- Menu Item Customers -->
<li>
    <a
        href="{{ route('admin.customers.index') }}"
        @click.prevent="selectedMenu = (selectedMenu === 'Customers' ? '' : 'Customers')"
        class="menu-item group"
        :class="(selectedMenu === 'Customers') ? 'menu-item-active' : 'menu-item-inactive'">
        <svg
            :class="(selectedMenu === 'Customers') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path
                d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                fill="currentColor" />
            <path
                d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z"
                fill="currentColor" />
        </svg>
        <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
            Customers
        </span>
        <svg
            class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
            :class="[(selectedMenu === 'Customers') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
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
        :class="(selectedMenu === 'Customers') ? 'block' :'hidden'">
        <ul
            :class="sidebarToggle ? 'lg:hidden' : 'flex'"
            class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
            <li>
                <a
                    href="{{ route('admin.customers.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'customerList' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                    Customer List
                </a>
            </li>
        </ul>
    </div>
</li>

<!-- System Orders Group -->
<li>
    <a
        href="#"
        @click.prevent="selectedMenu = (selectedMenu === 'SystemOrders' ? '' : 'SystemOrders')"
        class="menu-item group"
        :class="(selectedMenu === 'SystemOrders') ? 'menu-item-active' : 'menu-item-inactive'">
        <svg
            :class="(selectedMenu === 'SystemOrders') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path
                d="M7 2C5.34315 2 4 3.34315 4 5V6H3C2.44772 6 2 6.44772 2 7V8C2 8.55228 2.44772 9 3 9H4V20C4 21.6569 5.34315 23 7 23H17C18.6569 23 20 21.6569 20 20V9H21C21.5523 9 22 8.55228 22 8V7C22 6.44772 21.5523 6 21 6H20V5C20 3.34315 18.6569 2 17 2H7ZM7 4H17C17.5523 4 18 4.44772 18 5V6H6V5C6 4.44772 6.44772 4 7 4ZM4 11H20V20C20 20.5523 19.5523 21 19 21H5C4.44772 21 4 20.5523 4 20V11ZM6 12H18V18H6V12Z"
                fill="currentColor" />
        </svg>
        <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
            System Orders
        </span>
        <svg
            class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
            :class="[(selectedMenu === 'SystemOrders') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
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
        :class="(selectedMenu === 'SystemOrders') ? 'block' :'hidden'">
        <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
            <li>
                <a
                    href="{{ route('admin.order-fulfillment-list') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'orderList' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                    Admin Import Files
                </a>
            </li>
            <li>
                <a
                    href="{{ route('admin.submitted-orders') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'orderCreate' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                    Sent to Supplier
                </a>
            </li>
        </ul>
    </div>
    <!-- Dropdown Menu End -->
</li>

<!-- Customer Orders Group -->
<li>
    <a
        href="#"
        @click.prevent="selectedMenu = (selectedMenu === 'CustomerOrders' ? '' : 'CustomerOrders')"
        class="menu-item group"
        :class="(selectedMenu === 'CustomerOrders') ? 'menu-item-active' : 'menu-item-inactive'">
        <svg
            :class="(selectedMenu === 'CustomerOrders') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path
                d="M7 2C5.34315 2 4 3.34315 4 5V6H3C2.44772 6 2 6.44772 2 7V8C2 8.55228 2.44772 9 3 9H4V20C4 21.6569 5.34315 23 7 23H17C18.6569 23 20 21.6569 20 20V9H21C21.5523 9 22 8.55228 22 8V7C22 6.44772 21.5523 6 21 6H20V5C20 3.34315 18.6569 2 17 2H7ZM7 4H17C17.5523 4 18 4.44772 18 5V6H6V5C6 4.44772 6.44772 4 7 4ZM4 11H20V20C20 20.5523 19.5523 21 19 21H5C4.44772 21 4 20.5523 4 20V11ZM6 12H18V18H6V12Z"
                fill="currentColor" />
        </svg>
        <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
            Customer Orders
        </span>
        <svg
            class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
            :class="[(selectedMenu === 'CustomerOrders') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
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
        :class="(selectedMenu === 'CustomerOrders') ? 'block' :'hidden'">
        <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
            <li>
                <a
                    href="{{ route('admin.customer-uploaded-files-list') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'orderList' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                    Customer Import Files
                </a>
            </li>
            <li>
                <a
                    href="{{ route('admin.all-orders') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'allOrders' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                    All Orders
                </a>
            </li>
            <li>
                <a
                    href="{{ route('admin.api-orders') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'apiOrders' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                    API Orders
                </a>
            </li>
        </ul>
    </div>
    <!-- Dropdown Menu End -->
</li>

<!-- User Tier -->
<li>
    <a href="{{ route('admin.user-tiers.index') }}" class="menu-item group"
        :class="(selectedMenu === 'UserTier') ? 'menu-item-active' : 'menu-item-inactive'">
        <svg
            :class="(selectedMenu === 'UserTier') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path
                d="M5 16L3 6L8.5 10L12 4L15.5 10L21 6L19 16H5ZM19 16C19 16.6 18.6 17 18 17H6C5.4 17 5 16.6 5 16"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                fill="none" />
            <path
                d="M5 16V19C5 19.6 5.4 20 6 20H18C18.6 20 19 19.6 19 19V16"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                fill="none" />
        </svg>
        <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
            User Tier
        </span>
        <svg
            class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
            :class="[(selectedMenu === 'UserTier') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
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
        :class="(selectedMenu === 'UserTier') ? 'block' :'hidden'">
        <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
            <li>
                <a href="{{ route('admin.user-tiers.index') }}" class="menu-dropdown-item group"
                    :class="page === 'userTier' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                    User Tier List
                </a>
            </li>
        </ul>
    </div>
    <!-- Dropdown Menu End -->
</li>

<!-- Design Management -->
<li>
    <a
        href="#"
        @click.prevent="selectedMenu = (selectedMenu === 'DesignManagement' ? '' : 'DesignManagement')"
        class="menu-item group"
        :class="(selectedMenu === 'DesignManagement') ? 'menu-item-active' : 'menu-item-inactive'">
        <svg
            :class="(selectedMenu === 'DesignManagement') ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path
                d="M12 2L2 7L12 12L22 7L12 2Z"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round" />
            <path
                d="M2 17L12 22L22 17"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round" />
            <path
                d="M2 12L12 17L22 12"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
        <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
            Design Management
        </span>
        <svg
            class="menu-item-arrow absolute right-2.5 top-1/2 -translate-y-1/2 stroke-current"
            :class="[(selectedMenu === 'DesignManagement') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'lg:hidden' : '' ]"
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
        :class="(selectedMenu === 'DesignManagement') ? 'block' :'hidden'">
        <ul :class="sidebarToggle ? 'lg:hidden' : 'flex'" class="flex flex-col gap-1 mt-2 menu-dropdown pl-9">
            <li>
                <a
                    href="{{ route('admin.design.index') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'design-list' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                    Design List
                </a>
            </li>
            <li>
                <a
                    href="{{ route('admin.design.dashboard') }}"
                    class="menu-dropdown-item group"
                    :class="page === 'design-dashboard' ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive'">
                    Dashboard
                </a>
            </li>
        </ul>
    </div>
    <!-- Dropdown Menu End -->
</li>