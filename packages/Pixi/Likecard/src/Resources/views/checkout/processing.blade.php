@extends('shop::layouts.master')

@section('page_title')
    {{ __('shop::app.checkout.success.title') }}
@endsection

@section('content-wrapper')
    <div class="container text-center" style="padding: 40px 0;">
        <h2>{{ __('shop::app.checkout.payment.processing-payment') }}</h2>
        <p>{{ __('shop::app.checkout.payment.processing-message') }}</p>

        <!-- You can add a loading spinner here -->
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // The Cart ID passed from the controller
        const cartId = "{{ $cartId }}";
        const checkStatusUrl = "{{ route('edfapay.check_status') }}";

        let attempts = 0;
        const maxAttempts = 15; // Stop checking after ~30 seconds

        const interval = setInterval(function() {
            attempts++;

            if (attempts > maxAttempts) {
                clearInterval(interval);
                // Optionally redirect to a failure or "contact support" page
                window.location.href = "{{ route('shop.checkout.cart.index') }}";
                return;
            }

            fetch(`${checkStatusUrl}?cart_id=${cartId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // The order is created! Stop polling and redirect.
                        clearInterval(interval);
                        window.location.href = data.redirect_url;
                    }
                    // If status is 'pending', the loop will just continue.
                })
                .catch(error => {
                    console.error('Error checking payment status:', error);
                    clearInterval(interval);
                });
        }, 2000); // Check every 2 seconds
    });
</script>
@endpush
