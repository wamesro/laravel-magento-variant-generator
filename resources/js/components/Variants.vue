<template>
    <div>
        <h1 class="mb-3 text-90 font-normal text-2xl">{{ __('Laravel Magento Variant Generator') }}</h1>
        <div class="flex" style="">
            <div class="relative h-9 flex-no-shrink mb-6">
            </div>
            <div class="w-full flex items-center mb-6"><div class="flex w-full justify-end items-center mx-3"></div> <div class="flex-no-shrink ml-auto">
                <button class="btn btn-default btn-primary"
                        :disabled="!placePatternsButton"
                        @click="placePatterns">
                    {{ __('Place patterns') }}
                </button>
            </div>
            </div>
        </div>
        <div
            v-for="product in products"
            :key="product.id"
        >
            <card
                class="flex flex-col"
                style="min-height: 300px;padding: 2em;"
            >
                <h3>{{ product.title }}</h3>
                <br>
                <div
                    v-for="mockup in getMockupTables(product.id)"
                    :key="mockup.mockup_id"
                >
                    <vuetable ref="vuetable"
                              :fields="table.fields"
                              :api-mode="false"
                              class="width-100"
                              :css="table.css.table"
                              :data="getMockupTableData(mockup)"
                    >
                        <div slot="enabled" slot-scope="props">
                        <span v-if="props.rowData.enabled === true" class="ui teal label">
                            <input
                                type="checkbox"
                                class="checkbox"
                                checked
                            >
                        </span>
                            <span v-else class="ui pink label">
                            <input
                                type="checkbox"
                                class="checkbox"
                            >
                        </span>
                        </div>


                        <div slot="image" slot-scope="props">
                            <img :src="props.rowData.image" :alt="props.rowData.image">
                        </div>

                        <div slot="pattern" slot-scope="props">
                            <v-select :options="props.rowData.patterns" label="basename" v-model="props.rowData.pattern" v-on:input="selectBoxChanged($event, mockup.mockup_id, props.rowData.id)">
                                <template slot="option" slot-scope="option">
                                    <div class="select-option-value">
                                        <img :src="option.url" class="select-option-img"/>
                                        <span class="ml-3">{{ option.basename }}</span>
                                    </div>
                                </template>
                            </v-select>
                        </div>
                    </vuetable>
                </div>
            </card>
            <hr>
        </div>
    </div>
</template>

<script>
    import Vuetable from 'vuetable-2'
    import vSelect from 'vue-select'

    export default {
        components: {
            Vuetable,
            vSelect
        },
        data: () => ({
            products: [],
            mockupProducts: [],
            mockupsColors: [],
            patternSelectBox: [],
            placePatternsButton: false,
            table: {
                fields: [
                    {
                        name: "enabled",
                        title: "Enabled",
                        titleClass: 'center aligned w-16',
                        dataClass: 'center aligned'
                    },
                    {
                        name: "image",
                        title: "",
                        titleClass: "text-right w-16",
                        dataClass: "text-right"
                    },
                    {
                        name: "color",
                        title: "Color",
                        titleClass: "text-center",
                        dataClass: "text-center"
                    },
                    {
                        name: "pattern",
                        title: "Pattern",
                        titleClass: "text-center",
                        dataClass: "text-center width-40"
                    },
                ],
                css: {
                    table: {
                        tableWrapper: '',
                        tableHeaderClass: '',
                        tableBodyClass: 'vuetable-semantic-no-top',
                        tableClass: 'ui blue selectable unstackable celled table width-100',
                        loadingClass: 'loading',
                        ascendingIcon: 'blue chevron up icon',
                        descendingIcon: 'blue chevron down icon',
                        ascendingClass: 'sorted-asc',
                        descendingClass: 'sorted-desc',
                        sortableIcon: 'grey sort icon',
                        handleIcon: 'grey sidebar icon',
                    },
                }
            }
        }),
        mounted() {
            this.getVariants();
        },
        methods: {
            getVariants() {
                Nova.request()
                    .get('/nova-vendor/laravel-magento-variant-generator/' + this.$route.params.id +'/variants/get')
                    .then(response => {
                        this.mockupsColors = response.data.data;
                        this.getProductsByMockupIds()
                    });
            },
            getProductsByMockupIds() {
                Nova.request()
                    .get('/nova-vendor/laravel-magento-variant-generator/' + this.$route.params.id +'/variants/get/products')
                    .then(response => {
                        this.products = response.data.products;
                        this.mockupProducts = response.data.mockups;
                        this.placePatternsButton = true;
                    });
            },
            getMockupTables(productId) {
                let mockups = this.mockupProducts.filter(x => parseInt(x.product_id) === parseInt(productId));
                return mockups;
            },
            getMockupTableData(mockup) {
                let mockups = this.mockupsColors.filter(x => parseInt(x.mockup_id) === parseInt(mockup.mockup_id));

                let tableData = [];

                mockups.forEach(x => {
                    tableData.push(
                        {
                            id: x.id,
                            enabled: x.enabled,
                            image: x.image,
                            color: x.color,
                            pattern: x.pattern,
                            patterns: x.patterns
                        },
                    );
                });
                return tableData;
            },
            selectBoxChanged(event, mockupId, variantId) {
                this.changePattern(variantId, event);
                if (event === null) {
                    let mockupDefaults = this.mockupsColors.find(x => parseInt(x.id) === parseInt(variantId));
                    this.$refs.vuetable.find(x => {
                        return x.$options.propsData.data.find(x => {
                            if (x.id === variantId) {
                                x.pattern = mockupDefaults.pattern;
                                this.changePattern(variantId, mockupDefaults.pattern);
                            }
                        })
                    });
                }
            },
            changePattern(variantId, pattern) {
                if (pattern === null) {
                    return;
                }
                const formData = new FormData();
                formData.append('patternBasename', pattern.basename);
                const config = {
                    headers:{'Content-Type' : 'multipart/form-data'}
                };
                Nova.request()
                    .post('/nova-vendor/laravel-magento-variant-generator/'+variantId+'/pattern/change',
                        formData,
                        config)
                    .then(response => {

                    });
            },
            placePatterns() {
                this.$router.push('/laravel-magento-variant-generator-place-patterns/'+ this.$route.params.id);
            }
        }
    }
</script>

<style lang="scss" scoped>
/deep/ .width-100 {
    width: 100%;
}

/deep/ .select-option-img {
    width: 64px;
    height: 64px;
}
/deep/ .select-option-value {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
}

/deep/ .width-40 {
    width: 40%;
}
</style>
