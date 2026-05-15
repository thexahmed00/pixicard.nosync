<template>
    <div class="max-w-full max-sm:w-full">
        <span
            class="flex items-center gap-2.5"
            v-html="product.priceHtml"
        >
        </span>

        <div
            v-if="product.downloadableSamples.length"
            class="sample-list mb-6 mt-4"
        >
            <label class="mb-3 flex font-medium">
                {{ $t('pos.home.product.view.type.downloadable.samples') }}
            </label>

            <ul>
                <li
                    class="mb-2"
                    v-for="(sample, key) in product.downloadableSamples"
                    :key="key"
                >
                    <a 
                        :href="sample?.url || sample?.fileUrl" 
                        class="text-blue-700"
                        target="_blank"
                    >
                        {{ sample?.translations[0]?.title }}
                    </a>
                </li>
            </ul>
        </div>

        <template v-if="product.downloadableLinks.length">
            <label class="mb-4 mt-8 flex font-medium max-sm:mb-1.5 max-sm:mt-3">
                {{ $t('pos.home.product.view.type.downloadable.links') }}
            </label>

            <div class="grid gap-4 max-sm:gap-1">
                <div
                    class="flex flex-col select-none gap-y-2"
                    v-for="(link, key) in product.downloadableLinks"
                    :key="key"
                >
                    <div class="flex gap-x-2">
                        <div class="flex items-center">
                            <v-field
                                type="checkbox"
                                :name="`links[${key}]`"
                                :value="link.id"
                                :id="link.id"
                                class="peer hidden"
                                rules="required"
                                :label="$t('pos.home.product.view.type.downloadable.links')"
                            />
                            
                            <label
                                class="icon-un-checked peer-checked:icon-checked text-navyBlue peer-checked:text-navyBlue cursor-pointer text-2xl"
                                :for="link.id"
                            ></label>
                            
                            <label
                                :for="link.id"
                                class="cursor-pointer max-sm:text-sm ltr:ml-1 rtl:mr-1"
                            >
                                {{ `${link.translations[0]?.title} + ${outlet.formatPrice(link.price)}` }}
                            </label>
                        </div>

                        <a
                            v-if="
                                link.sampleFile
                                || link.sampleUrl
                            "
                            :href="
                                link.sampleFile
                                || link.sampleUrl
                            "
                            target="_blank"
                            class="text-blue-700 max-sm:text-sm"
                        >
                            {{ $t('pos.home.product.view.type.downloadable.samples') }}
                        </a>
                    </div>

                    <v-error-message
                        :name="`links[${key}]`"
                        class="text-sm text-red-500"
                    />
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
    import { useOutlet } from '@src/composable/outlet';

    defineProps({
        product: {
            type: Object,
            required: true,
        },
    });

    const outlet = useOutlet();
</script>