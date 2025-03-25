@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<section class="about-us-section product-sans-regular">
    <div
        class="relative max-w-screen-xl mx-auto h-[300px] flex items-center justify-center text-white bg-cover bg-center"
        style="background-image: url('../assets/images/bg_breadcrumb.jpg')">
        <!-- Overlay tối -->
        <div class="absolute inset-0 bg-black/40"></div>

        <!-- Nội dung chính -->
        <div class="relative z-10 text-center">
            <h1 class="text-4xl font-semibold">Privacy Policy</h1>
            <div class="mt-2">
                <a href="/" class="text-gray-200 hover:text-gray-400">Home</a>
                <span class="mx-2 text-gray-300">›</span>
                <span class="text-orange-400">Privacy Policy</span>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto py-10 mt-10">
        <h1 class="text-3xl mb-4 font-semibold product-sans-bold">
            Privacy Policy
        </h1>

        <p class="mt-2">Welcome to HM Fulfill's Privacy Policy!</p>

        <p class="mt-2">
            HM Fulfill, Inc. ("we", "our", "us", or "HM Fulfill") respects the privacy of
            its users and is fully committed to protect their personal data and
            use it in accordance with data privacy laws. This Privacy Policy
            describes how we collect, use, and process any personal data that we
            collect from you—or you provide to us—in connection with your use of
            our website (www.hmfulfill.com) or our mobile apps and our
            print-on-demand services (collectively, "Services"). By accessing or
            using our Services, you signify your understanding of the terms set
            out in this Privacy Policy.
        </p>

        <p class="mt-2">
            If you use our Services only for your personal use, you are to be
            considered as the "User" and for the purpose of the General Data
            Protection Regulation (GDPR), we are the data controller.
        </p>

        <p class="mt-2">
            Note that while our Services may contain links to other websites or
            services, we are not responsible for each respective website's or
            service's privacy practices and encourage you to be aware of this when
            you leave our Services and carefully read the privacy statements of
            each and every website and service you visit. This Privacy Policy does
            not apply to third-party websites and services. If you wish to contact
            HM Fulfill regarding your personal data or this Privacy Policy, please
            contact us at
            <a style="color: #f7961d" href="mailto:admin@hmfulfill.com" class="text-blue-500">admin@hmfulfill.com</a>.
        </p>

        <h2 class="text-xl font-semibold mt-6">
            What information do we collect?
        </h2>
        <p class="mt-2">
            We collect information from you when you register on the site, place
            an order, enter any site promotions, respond to surveys, communication
            such as e-mail, or participate in other site features. When ordering
            or registering, we may ask for your name, e-mail address, mailing
            address, phone number or other information.
        </p>

        <h2 class="text-xl font-semibold mt-6">
            How do we use your information?
        </h2>
        <p class="mt-2">
            We require this information to understand your needs and provide you
            with a better service, and in particular for the following reasons:
        </p>
        <ul class="list-disc ml-6 mt-2">
            <li>Internal record keeping.</li>
            <li>To quickly process your transactions.</li>
            <li>
                To administer a contest, promotion, survey or other site feature.
            </li>
            <li>
                To personalize your site experience and to allow us to deliver the
                type of content and products in which you are most interested.
            </li>
            <li>
                We may use the information to improve our products and services.
            </li>
            <li>
                We may periodically send promotional emails about new products,
                special offers or other information which we think you may find
                interesting using the email address which you have provided. All
                emails and newsletters from this site allow you to opt out of
                further mailings.
            </li>
        </ul>

        <h2 class="text-xl font-semibold mt-6">Cookie/Tracking Technology</h2>
        <p class="mt-2">
            Our website may use cookie and tracking technology depending on the
            features offered. Cookie and tracking technologies are useful for
            helping us provide a better web experience, such as remembering and
            processing items in your shopping cart and providing improved content
            and services based on previous or current site activity. Cookies also
            allow us to customize our web site for visitors by gathering
            information such as browser type and operating system, tracking the
            number of visitors to our site, and understanding how visitors use our
            site. This helps us compile aggregate data about site traffic and site
            interaction so that we can offer better site experiences and tools in
            the future. Personal information cannot be collected via cookies and
            other tracking technology, however, if you previously provided
            personally identifiable information, cookies may be tied to such
            information. We may contract with third-party service providers to
            assist us in better understanding our site visitors. These service
            providers are not permitted to use the information collected on our
            behalf except to help us conduct and improve our business.
        </p>

        <h2 class="text-xl font-semibold mt-6">Security</h2>
        <p class="mt-2">
            We apply a variety of security measures to maintain the safety of all
            personal information. Your personal information is contained behind
            secured networks and is only accessible by a limited number of persons
            who have special access rights to such systems, and are required to
            keep the information confidential. When you place orders or access
            your personal information, we offer the use of a secure server. All
            sensitive/credit information you provided is transmitted via Secure
            Socket Layer (SSL) technology and then encrypted into our databases to
            be only accessed as stated above.
        </p>

        <h2 class="text-xl font-semibold mt-6">Distribution of Information</h2>
        <p class="mt-2">
            We do not sell, trade, or otherwise transfer to outside parties your
            personally identifiable information unless we provide you with advance
            notice, except as described below. The term "outside parties" does not
            include us. It also does not include website hosting partners and
            other parties who assist us in operating our website, conducting our
            business, or servicing you, so long as those parties agree to keep
            this information confidential. We may also release your information
            when we believe release is appropriate to comply with the law, protect
            against fraud or unauthorized transactions, enforce our site policies,
            or protect ours or others' rights, property, or safety. However,
            non-personally identifiable visitor information may be provided to
            other parties for marketing, advertising, or other uses.
        </p>

        <h2 class="text-xl font-semibold mt-6">
            Third Party Links (In Future)
        </h2>
        <p class="mt-2">
            In an attempt to provide you with increased value, we may include
            third party links on our site. These linked sites have separate and
            independent privacy policies. We therefore have no responsibility or
            liability for the content and activities of these linked sites.
            Nonetheless, we seek to protect the integrity of our site and welcome
            any feedback about these linked sites (including if a specific link
            does not work).
        </p>

        <p class="mt-4">Thanks for reading.</p>
        <p class="mt-4">Regards</p>
    </div>
</section>
@endsection