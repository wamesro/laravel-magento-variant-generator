<template>
    <div>
        <div class="flex" style="">
            <div class="relative h-9 flex-no-shrink mb-6">
            </div>
            <div class="w-full flex items-center mb-6"><div class="flex w-full justify-end items-center mx-3"></div> <div class="flex-no-shrink ml-auto">
                <button class="btn btn-default btn-primary" @click="exportReady">{{ __('Main Page') }}</button>
            </div>
            </div>
        </div>
        <card
            class="generating-wrapper"
        >
            <span v-if="status === ''">
                <h1 class="text-center">{{ __('Počkajte prosím') }}</h1>
                <Skeleton/>
            </span>

            <span v-if="status === 5">
                <h1 class="text-center">{{ __('Produkty boli pridané do zoznamu na vygenerovanie') }}</h1>
                <h3 class="text-center">{{ __('Stránku môžete bezpečne opustiť. Notifikujeme Vás, keď budú produkty vygenerované.') }}</h3>
            </span>
            <span  v-if="status === 6">
                <h1 class="text-center">{{ __('Produkty čakajú na vygenerovanie') }}</h1>
                <h3 class="text-center">{{ __('Stránku môžete bezpečne opustiť. Notifikujeme Vás, keď budú produkty vygenerované.') }}</h3>
            </span>
            <span v-if="status === 7" class="text-center">
                <h1 class="text-center">{{ __('Produkty už boli vygenerované') }}</h1>
                <h3 class="text-center">{{ __('Stránku môžete bezpečne opustiť. CSV súbor si môžete stiahnúť tu.') }}</h3>
                <button class="btn btn-primary" style="text-align: center;padding: 1em;margin: 1em;border-radius: 15px;" @click="downloadCsv">{{ __('Stiahnúť CSV') }}</button>
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
            download: ''
        }),
        mounted() {
            this.exportReady();
        },
        methods: {
            exportReady() {
                Nova.request()
                    .post('/nova-vendor/laravel-magento-variant-generator/export_ready/' + this.$route.params.id)
                    .then(response => {
                        this.status = response.data.status;
                        if (response.data.status === 5) {
                            this.$toasted.show('Produkty boli pridané do zoznamu na vygenerovanie', { type: 'success' });
                            this.variants = response.data.variants;
                        }
                        if (response.data.status === 6) {
                            this.$toasted.show('Produkty už čakajú na vygenerovanie', { type: 'info' });
                            this.variants = response.data.variants;
                        }
                        if (response.data.status === 7) {
                            this.$toasted.show('Produkty už boli úspešne vygenerované', { type: 'info' });
                            this.variants = response.data.variants;
                        }
                    });
            },
            downloadCsv() {
                this.download = window.location.origin + '/nova-vendor/laravel-magento-variant-generator/csv/' + this.$route.params.id;
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
