<x-marketplace::seller.layouts>
    <x-slot:title>
        Add Balance 
    </x-slot>

    {{-- The form tag now wraps the entire content, including the buttons at the bottom --}}
    <x-admin::form
        :action="route('seller.wallet.store')"
        enctype="multipart/form-data"
    >
        @csrf
        {{-- Header Section --}}
        <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
            <p class="text-xl text-gray-800 dark:text-white font-bold">
                Add Balance to Wallet
            </p>
            {{-- Buttons have been moved from this top section --}}
        </div>

        {{-- Form Fields Section --}}
        <div class="mt-3.5 p-4 bg-white dark:bg-gray-900 rounded box-shadow">
            
            {{-- Amount Input --}}
            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    Amount
                </x-admin::form.control-group.label>

                <div class="max-w-xs">
                        <x-admin::form.control-group.control
                            type="number"
                            name="amount"
                            :value="old('amount')"
                            rules="required"
                            label="Amount"
                            placeholder="Enter amount"
                        />
                    </div>

                <x-admin::form.control-group.error control-name="amount" />
            </x-admin::form.control-group>

            {{-- Recharge Option with New Custom Design --}}
            <x-admin::form.control-group class="!mb-0">
                <x-admin::form.control-group.label class="required">
                    Recharge Option
                </x-admin::form.control-group.label>
                
                <div class="flex gap-4 mt-2">
                    {{-- Payment Option --}}
                    <div>
                        <input 
                            type="radio" 
                            name="recharge_option" 
                            value="payment" 
                            id="recharge_option_payment" 
                            class="hidden peer"
                            {{ old('recharge_option', 'payment') == 'payment' ? 'checked' : '' }}
                        >
                        <label 
                            for="recharge_option_payment" 
                            class="flex items-center gap-2 px-4 py-2 border-2 rounded-lg cursor-pointer text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 peer-checked:border-blue-600 peer-checked:text-blue-600 dark:peer-checked:border-blue-500 dark:peer-checked:text-blue-500 transition-all duration-200"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            <span>Payment</span>
                        </label>
                    </div>

                    {{-- Credit Request Option --}}
                    <div>
                        <input 
                            type="radio" 
                            name="recharge_option" 
                            value="credit" 
                            id="recharge_option_credit" 
                            class="hidden peer"
                            {{ old('recharge_option') == 'credit' ? 'checked' : '' }}
                        >
                        <label 
                            for="recharge_option_credit" 
                            class="flex items-center gap-2 px-4 py-2 border-2 rounded-lg cursor-pointer text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 peer-checked:border-blue-600 peer-checked:text-blue-600 dark:peer-checked:border-blue-500 dark:peer-checked:text-blue-500 transition-all duration-200"
                        >
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>Request Credit</span>
                        </label>
                    </div>
                </div>

                <x-admin::form.control-group.error control-name="recharge_option" />
            </x-admin::form.control-group>
        </div>

        {{-- Form Actions/Buttons Section (Moved to the bottom) --}}
        <div class="flex justify-end gap-x-2.5 items-center mt-4">
            <a
                href="{{ route('seller.wallet.index') }}"
                class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
            >
                Back
            </a>

            <button
                type="submit"
                class="primary-button"
            >
                Submit
            </button>
        </div>
    </x-admin::form>
</x-marketplace::seller.layouts>

