@extends('layouts.app')

@section('title', 'Terms & Conditions')

@section('content')

<section class="about-us-section product-sans-regular">
    <div
        class="relative max-w-screen-xl mx-auto h-[300px] flex items-center justify-center text-white bg-cover bg-center"
        style="background-image: url('../assets/images/bg_breadcrumb.jpg')">
        <!-- Overlay tối -->
        <div class="absolute inset-0 bg-black/40"></div>

        <!-- Nội dung chính -->
        <div class="relative z-10 text-center">
            <h1 class="text-4xl font-semibold">Terms & Conditions</h1>
            <div class="mt-2">
                <a href="/" class="text-gray-200 hover:text-gray-400">Home</a>
                <span class="mx-2 text-gray-300">›</span>
                <span class="text-orange-400">Terms & Conditions</span>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto py-10 mt-10">
        <h1 class="text-3xl mb-4 font-semibold product-sans-bold">
            Terms & Conditions
        </h1>

        <p class="mt-2">
            Hi, we're HM Fulfill and sincerely welcome to the HM Fulfill Terms of Services
            and Policy Page. This page will summarize all necessary information
            that you can take into consideration when you are using HM Fulfill's
            services. The policies below are based on the current standard/current
            model of most platform services, and also connected with consumer
            rights which we deem are the best points to join HM Fulfill System. Being
            HM Fulfill's clients will give you consistent great experiences and know
            what to expect from us in all situations.
        </p>

        <p class="mt-2">
            By signing up for a HM Fulfill Account (as defined in Section 1) together
            with using any HM Fulfill Services (as defined below), you agree to abide
            by these following terms and conditions set herein.
        </p>

        <h2 class="text-xl font-semibold mt-6">Services</h2>
        <p class="mt-2">
            Services offered by HM Fulfill under the Terms of Service include various
            products and services to help you sell goods to buyers. All provided
            by HM Fulfill are referred to in these Terms of Services as the
            "Services". Any new features or tools which are added to the current
            Services shall also be subject to the Terms of Service. You can review
            the current version of the Terms of Service at any time at
            <a style="color: #f7961d" href="https://www.hmfulfill.com/terms" class="text-blue-500">https://www.hmfulfill.com/terms</a>. HM Fulfill reserves the right to update and change the Terms of Service
            by posting updates and changes to the HM Fulfill website. Your use of the
            website, in any manner, constitutes your agreement to all such terms,
            conditions, and notices, as may be updated and amended from time to
            time. If you do not agree to this Agreement, do not use the Site or
            any other Services.
        </p>

        <h2 class="text-xl font-semibold mt-6">Eligibility</h2>
        <p class="mt-2">
            Minimum Age. The Services and Website are available only to persons
            who are the age of majority and can form legally binding contracts
            under applicable law. Without limiting the foregoing, the Services and
            Website are not intended to be used by individuals under the age of
            18. If you do not qualify, please do not use the Services or access
            the Website.
        </p>

        <h2 class="text-xl font-semibold mt-6">Use Restrictions</h2>
        <p class="mt-2">
            This is an agreement for Services, and you are not granted a license
            to any software by these Terms. You will not, directly or indirectly:
            reverse engineer, decompile, disassemble, or otherwise attempt to
            discover the source code, object code, or underlying structure, ideas,
            or algorithms of or included in the Services or any software,
            documentation or data related to the Services ("Software"); modify,
            translate or create derivative works based on the Services or any
            Software; or copy (except for archival purposes), distribute, pledge,
            assign or otherwise transfer or encumber rights to the Services or any
            Software; use the Services or any Software for time sharing or service
            bureau purposes or otherwise for the benefit of a third party; or
            remove any proprietary notices or labels.
        </p>

        <h2 class="text-xl font-semibold mt-6">Intellectual Property</h2>
        <p class="mt-2">
            We do not claim any intellectual property rights over the content you
            provide to HM Fulfill. All of your content remains yours. When providing
            content using the Services (directly or indirectly), you grant us a
            non-exclusive, worldwide, royalty-free, sublicensable (through
            multiple tiers) right to exercise any and all copyright, trademark,
            patent, publicity, moral, database, and/or other intellectual property
            rights (collectively, "IP Rights") you have in that content or
            associated with your store in connection with our provision of the
            Services, in any media known now or developed in the future.
        </p>

        <h2 class="text-xl font-semibold mt-6">Limitations of Liability</h2>
        <p class="mt-2">
            HM Fulfill and its suppliers and affiliates assume no responsibility with
            respect to your or your user's use of the website, software, or
            services and will not be liable for any consequential, indirect,
            incidental, punitive, extraordinary, exemplary or special damages,
            including, without limitation, loss of use, business interruptions,
            loss of data, loss of profits, and lost revenue, whether such damages
            are alleged in tort, contract or any other legal or equitable theory,
            and whether or not HM Fulfill is aware of the possibility of such damages.
        </p>

        <h2 class="text-xl font-semibold mt-6">Carriers/Shipping Services</h2>
        <p class="mt-2">
            Our shipping lines are relatively close to the time frame of shipment
            committed for each method. However, there will be a tiny number of
            orders arriving not in time. For sellers who continue using Epacket
            during this period, you will have to accept the fact that tracking
            will be active later than 48 hours (as normal). The time frame to
            transport to the US takes 12 - 20 days (even 21 - 30 days depending on
            various factors). Furthermore, the packets may be confiscated by the
            Customs agency or returned to the sender. For these cases, HM Fulfill will
            take responsibility to reproduce and reship to customers. We do not
            accept refund base cost as we are the ones who are at a loss in this
            case because the post office will not provide compensation.
        </p>

        <h2 class="text-xl font-semibold mt-6">Working Time and Support</h2>
        <p class="mt-2">
            Our support time is during working hours and after work until 11:00
            pm. Time collecting orders and processing payment is latest at 17:00.
            All orders uploaded on HM Fulfill system after 17:00 will be processed the
            following day. Production time just starts when all information of
            orders (customer information, design, and payment as well) are fully
            done. You can only cancel an order within 24 hours. After 24 hours, we
            refuse to cancel.
        </p>

        <p class="mt-4">Thanks for reading.</p>
        <p class="mt-4">Regards</p>
    </div>
</section>
@endsection