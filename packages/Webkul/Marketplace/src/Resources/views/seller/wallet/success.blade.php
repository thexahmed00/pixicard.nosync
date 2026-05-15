<x-marketplace::seller.layouts>
    <x-slot:title>
        Add Balance 
    </x-slot>

<x-admin::form
        :action="route('seller.wallet.store')"
        enctype="multipart/form-data"
    >
     @csrf
        <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
            <p class="text-xl text-gray-800 dark:text-white font-bold">
                Add Balance to Wallet
            </p>

          
                <div class="flex gap-x-2.5 items-center">
                    <!-- Back Button -->
                    <a
                        href="{{ route('seller.wallet.index') }}"
                        class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                    > Back
                    </a>

                    <!-- Save Button -->
                    <button
                        type="submit"
                        class="primary-button"
                    >
                        Save
                    </button>
                </div>
          
        </div>
  <!-- Full Panel -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <div class="flex flex-col gap-2 flex-1 max-xl:flex-auto">
                <div class="box-shadow relative rounded bg-white p-4 dark:bg-gray-900">
                   
                    <!-- UserName -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            Amount
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="number"
                            name="amount"
                            :value="old('amount')"
                            rules="required"
                         
                        />

                        <x-admin::form.control-group.error control-name="username" />
                    </x-admin::form.control-group>

                    

                 
                </div>

                <div class="box-shadow relative rounded bg-white p-4 dark:bg-gray-900">
                 
                </div>
            </div>
            
            <div class="flex flex-col gap-2 flex-1 max-xl:flex-auto">
                <div class="box-shadow relative rounded bg-white p-4 dark:bg-gray-900">
  
               
                </div>

            
            </div>
        </div>
    </x-admin::form>
</x-marketplace::layouts>