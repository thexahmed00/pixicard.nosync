<template>
    <div class="relative flex items-center">
        <slot></slot>

        <i class="icon-calendar absolute text-xl text-dark ltr:right-2 rtl:left-2"></i>
    </div>
</template>

<script>
    import Flatpickr from "flatpickr";
    import "flatpickr/dist/flatpickr.css";
    
    export default {
        props: {
            name: String,

            value: String,

            allowInput: {
                type: Boolean,
                default: true,
            },

            disable: Array,

            minDate: String,

            maxDate: String,
        },

        data() {
            return {
                datepicker: null
            };
        },

        mounted() {
            let options = this.setOptions();

            this.activate(options);
        },

        methods: {
            setOptions() {
                return {
                    allowInput: this.allowInput ?? true,
                    disable: this.disable ?? [],
                    minDate: this.minDate ?? '',
                    maxDate: this.maxDate ?? '',
                    altFormat: "Y-m-d",
                    dateFormat: "Y-m-d",
                    weekNumbers: true,

                    onChange: (selectedDates, dateStr, instance) => {
                        this.$emit("onChange", dateStr);
                    }
                };
            },

            activate(options) {
                let element = this.$el.getElementsByTagName("input")[0];

                this.datepicker = new Flatpickr(element, options);
            },

            clear() {
                this.datepicker.clear();
            }
        }
    };
</script>