<template>
    <div>
        <div class="flex" style="">
            <div class="relative h-9 flex-no-shrink mb-6">
                <h1 class="text-90 font-normal text-2xl">
                    <button @click="$router.push('/laravel-magento-variant-generator-variants/'+ $route.params.id)" class="no-underline text-primary font-bold dim router-link-active" >←</button>
                    <span class="px-2 text-70">/</span> {{ __('Variants') }}
                </h1>
            </div>
            <div class="w-full flex items-center mb-6"><div class="flex w-full justify-end items-center mx-3"></div> <div class="flex-no-shrink ml-auto">
                <button class="btn btn-default btn-primary" @click="setPatterns">{{ __('Main Product') }}</button>
            </div>
            </div>
        </div>
        <card
            class="place-pattern-card-wrapper"
        >
            <div class="skeletons" v-if="mockups.length <1">
                <Skeleton width="120px" height="10px" />
                <Skeleton width="600px" height="600px" />
                <Skeleton width="600px" height="30px" />
            </div>
            <div class="skeletons" v-if="mockups.length <1">
                <Skeleton width="120px" height="10px" />
                <Skeleton width="600px" height="600px" />
                <Skeleton width="600px" height="30px" />
            </div>
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
    import { Skeleton } from 'vue-loading-skeleton';

    export default {
        components: {
            FabricPattern,
            Skeleton
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
            },
            setPatterns() {
                this.$router.push('/laravel-magento-variant-generator-main-product/'+this.$route.params.id+'?forceRefresh=true');
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
    .skeletons {
        display: flex;
        flex-direction: column;
        margin-bottom: 50px;
        &:last-child {
            margin-left: 2em;
        }
        /deep/ .pu-skeleton {
            &:last-child {
                margin-top: 10px;
            }
        }
    }
</style>
