<x-admin::layouts>
    <x-slot:title>
        Wallet Transactions for {{ $seller->name }}
    </x-slot>

    {{-- Add an ID to this H2 tag --}}
    <h2 class="mb-4 text-lg font-semibold" id="wallet-balance-display">
        Wallet Balance: {{ core()->currency($balance) }}
    </h2>
    
    <h2 class="mb-4 text-lg font-semibold">
        Transactions for {{ $seller->name }}
    </h2>
    
    <x-shop::datagrid
        :src="route('admin.marketplace.sellers.wallet', $seller->id)"
    >
    </x-shop::datagrid>
</x-admin::layouts>

