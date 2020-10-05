<template>
    <div>
        <h1 class="mb-3 text-90 font-normal text-2xl">{{ __('Laravel Magento Variant Generator') }}</h1>
        <div class="flex" style="">
            <div class="relative h-9 flex-no-shrink mb-6">
            </div>
            <div class="w-full flex items-center mb-6"><div class="flex w-full justify-end items-center mx-3"></div> <div class="flex-no-shrink ml-auto">
                <button class="btn btn-default btn-primary" @click="getPlacePatternsData">

                </button>
            </div>
            </div>
        </div>
        <card
            class="place-pattern-card-wrapper"
        >
            <FabricPattern
                v-for="mockup in mockups"
                :mockup="mockup"
                :key="mockup.id"
            />
        </card>
    </div>
</template>

<script>
    import FabricPattern from './parts/FabricPattern';

    export default {
        components: {
            FabricPattern
        },
        data: () => ({
            mockups: []
        }),
        mounted() {
            this.getPlacePatternsData();
        },
        methods: {
            getPlacePatternsData() {
                Nova.request()
                    .get('/nova-vendor/laravel-magento-variant-generator/' + this.$route.params.id +'/variants/get/place_patterns')
                    .then(response => {
                        this.mockups = response.data;
                    });
            }
        }
    }
</script>

<style lang="scss" scoped>
    .place-pattern-card-wrapper {
        min-height: 300px;
        padding: 2em;
        justify-content: center;
        align-items: center;
        display: flex;
        flex-wrap: wrap;
    }
</style>
