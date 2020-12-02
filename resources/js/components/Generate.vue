<template>
    <div>
        <div class="flex" style="">
            <div class="relative h-9 flex-no-shrink mb-6">
                <h1 class="text-90 font-normal text-2xl">
                    <button @click="$router.push('/laravel-magento-variant-generator-main-product/'+$route.params.id);" class="no-underline text-primary font-bold dim router-link-active" >←</button>
                    <span class="px-2 text-70">/</span> {{ __('Main Product') }}
                </h1>
            </div>
            <div class="w-full flex items-center mb-6"><div class="flex w-full justify-end items-center mx-3"></div> <div class="flex-no-shrink ml-auto">
                <button class="btn btn-default btn-primary">{{ __('New Product') }}</button>
            </div>
            </div>
        </div>
        <card
            class="generating-wrapper"
        >
            <span v-if="status === 11" class="text-center">
                <h1 class="text-center">{{ __('Produkty boli vygenerované') }}</h1>
                <h3 class="text-center">{{ __('Stránku môžete bezpečne opustiť. CSV súbor si môžete stiahnúť tu.') }}</h3>
                <button class="btn btn-primary" style="text-align: center;padding: 1em;margin: 1em;border-radius: 15px;" @click="downloadCsv">{{ __('Stiahnúť CSV') }}</button>
            </span>
            <span v-else>
                <h1 class="text-center">{{ __('Produkty zaradené do exportu') }}</h1>
                <Skeleton/>
            </span>
        </card>
        <card
            class="completed-patterns-wrapper"
        >
            <Skeleton v-if="variants.length < 1" width="250px" height="250px" count="6"/>
            <img v-for="variant in variants" :key="variant.id" :src="variant.final_image">
        </card>
        <iframe :src="download" style="display:none;"></iframe>
    </div>
</template>

<script>
    import { Skeleton } from 'vue-loading-skeleton';

    export default {
        components: {
            Skeleton
        },
        data: () => ({
            variants: [],
            status: '',
            download: '',
            enableRefresh: false
        }),
        mounted() {
            this.exportReady();
            setInterval(() => this.refresh(), 10000);
        },
        methods: {
            exportReady() {
                Nova.request()
                    .post('/nova-vendor/laravel-magento-variant-generator/export_ready/' + this.$route.params.id)
                    .then(response => {
                        this.status = response.data.status;
                        if (this.status === 4) {
                            this.$toasted.show('Products added to queue. You can leave this page right now.', { type: 'info' });
                            this.enableRefresh = true;
                            this.variants = [];
                        }
                        if (this.status === 5 || this.status === 6 || this.status === 7 || this.status === 8 || this.status === 9) {
                            this.$toasted.show('Product generating... We will notify you, when the products will be ready.', { type: 'info' });
                            this.enableRefresh = true;
                            this.variants = [];
                        }
                        if (this.status === 10) {
                            this.enableRefresh = false;
                            this.variants = response.data.variants;
                        }
                        if (this.status === 11) {
                            this.enableRefresh = false;
                            this.variants = response.data.variants;
                        }
                    });
            },
            downloadCsv() {
                this.download = window.location.origin + '/nova-vendor/laravel-magento-variant-generator/csv/' + this.$route.params.id;
            },
            refresh() {
                if (this.enableRefresh === true) {
                    this.exportReady();
                }
            }
        }
    }
</script>

<style lang="scss" scoped>
    .generating-wrapper {
        min-height: 50px;
        padding: 2em;
        justify-content: center;
        align-items: center;
        display: flex;
        flex-wrap: wrap;
    }
    .completed-patterns-wrapper {
        margin-top: 2em;
        min-height: 300px;
        padding: 2em;
        justify-content: left;
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        img {
            width: 250px;
            height: 250px;
            object-fit: contain;
        }
    }
</style>
