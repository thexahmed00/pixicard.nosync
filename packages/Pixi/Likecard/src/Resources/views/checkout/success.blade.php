<x-shop::layouts
    :has-header="true"
    :has-feature="false"
    :has-footer="true"
>
    {{-- The title will be dynamic based on the context --}}
    <x-slot:title>
        @if (isset($order))
            @lang('shop::app.checkout.success.thanks')
        @else
            @lang('Processing Payment')
        @endif
    </x-slot>

    {{-- 
        This is the main conditional block.
        It checks if an '$order' variable exists. If it does, it shows the "Success" content.
        If it does not exist, it shows the "Processing" content.
    --}}
    @if (isset($order))
        {{-- STATE 1: SUCCESS (Original content) --}}
        <div class="container mt-8 px-[60px] max-lg:px-8">
            <div class="grid place-items-center gap-y-5 max-md:gap-y-2.5">
                {{ view_render_event('bagisto.shop.checkout.success.image.before', ['order' => $order]) }}

                <img 
                    class="max-md:h-[100px] max-md:w-[100px]"
                    src="{{ bagisto_asset('images/thank-you.png') }}" 
                    alt="@lang('shop::app.checkout.success.thanks')" 
                    title="@lang('shop::app.checkout.success.thanks')"
                >

                <p class="text-xl max-md:text-sm">
                    @if (auth()->guard('customer')->user())
                        @lang('shop::app.checkout.success.order-id-info', [
                            'order_id' => '<a class="text-blue-700" href="'.route('shop.customers.account.orders.view', $order->id).'">'.$order->increment_id.'</a>'
                        ])
                    @else
                        @lang('shop::app.checkout.success.order-id-info', ['order_id' => $order->increment_id]) 
                    @endif
                </p>

                <p class="font-medium md:text-2xl">
                    @lang('shop::app.checkout.success.thanks')
                </p>
                
                <a href="{{ route('shop.home.index') }}">
                    <div class="w-max cursor-pointer rounded-2xl bg-navyBlue px-11 py-3 text-center text-base font-medium text-white max-md:rounded-lg max-md:px-6 max-md:py-1.5">
                        @lang('shop::app.checkout.cart.index.continue-shopping')
                    </div> 
                </a>
            </div>
        </div>
    @else
        {{-- STATE 2: PROCESSING (New content) --}}
        <div class="container text-center" style="padding: 80px 0;">
            <h2 class="text-2xl font-medium">Processing Your Payment</h2>
            <p class="text-zinc-500 mt-2">Please wait, do not close or refresh this page.</p>

            <!-- Loading Spinner -->
            <div class="mt-8">
                <svg class="animate-spin h-10 w-10 text-navyBlue mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>

        {{-- The JavaScript polling script to check for order completion --}}
        @push('scripts')
        <script>
document.addEventListener("DOMContentLoaded", function() {
        const cartId = "{{ $cartId ?? '' }}"; 
        const checkStatusUrl = "{{ route('edfapay.check_status') }}";

        if (!cartId) {
            console.error('Cart ID not found. Cannot check payment status.');
            return;
        }

        let attempts = 0;
        // Check 12 times, with a 5-second interval between checks.
        const maxAttempts = 18; // 12 attempts * 5 seconds = 60 seconds total
        const intervalTime = 10000; // 5 seconds in milliseconds

        const interval = setInterval(function() {
            attempts++;
            if (attempts > maxAttempts) {
                clearInterval(interval);
                // On timeout after 1 minute, redirect back to the cart
                window.location.href = "{{ route('shop.checkout.cart.index') }}";
                return;
            }

            fetch(`${checkStatusUrl}?cart_id=${cartId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        clearInterval(interval);
                        // The order is created, redirect to the real success URL
                        window.location.href = data.redirect_url;
                    }
                })
                .catch(error => {
                    console.error('Error checking payment status:', error);
                    clearInterval(interval);
                });
        }, intervalTime); // Poll every 5 seconds
    });
        </script>
        @endpush
    @endif
</x-shop::layouts>

