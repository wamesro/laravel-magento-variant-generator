<template>
    <div>
        <heading class="mb-6">Laravel Magento Variant Generator</heading>

        <div class="flex" style="">
            <div class="relative h-9 flex-no-shrink mb-6">
            </div>
                <div class="w-full flex items-center mb-6"><div class="flex w-full justify-end items-center mx-3"></div> <div class="flex-no-shrink ml-auto">
                    <button class="btn btn-default btn-primary"
                            :disabled="selectedMockups.map(x => x !== null ? x.id : null).filter(function (el) {return el != null;}) < 1 || disabledButton === true"
                            @click="submitForm">
                    {{ __('Add patterns') }}
                    </button>
                </div>
            </div>
        </div>
        <card
            class="flex flex-row"
            style="min-height: 300px;padding: 2em;"
        >
            <card
                class="bg-50 flex flex-col justify-center"
                style="width: 20em; padding: 1em; margin: 1em;"
                v-for="(product, index) in productList"
                :key="index"
            >
                <h3 class="text-center">{{ product.fields[2].value }}</h3>
                <img
                    v-if="productImages[index]"
                    :src="productImages[index]"
                    style="
                        max-width: 100%;
                        width: 100%;
                        height: 200px;
                        object-fit: cover;"
                >
                <img
                    v-else
                    src="http://placehold.it/250x250"
                    style="
                        max-width: 100%;
                        width: 100%;
                        height: 200px;
                        object-fit: cover;"
                >
                <v-select
                    v-if="mockupList.length > 0"
                    style="margin-top: 1em"
                    label="title"
                    index="id"
                    v-model="selectedMockups[index]"
                    :value="selectedMockups[index]"
                    :options="mockupList.find(x => x.mainId === product.id.value) ? mockupList.find(x => x.mainId === product.id.value).items : []"
                    @input="setMockup($event, product.id.value, index)"
                ></v-select>
            </card>
        </card>
    </div>
    </template>

<script>
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

export default {
    components: {
        vSelect
    },
    data() {
        return {
            productList: [],
            mockupList: [],
            mockupImages: [],
            productImages: [],
            selectedMockups: [],
            disabledButton: false
        }
    },
    mounted() {
        this.getProductList();
    },
    methods: {
        getIndex(list, id) {
            return list.findIndex((e) => e.id.value == id);
        },
        getProductList() {
            Nova.request().get('/nova-api/products').then(response => {
                this.productList = response.data.resources;
                response.data.resources.forEach(resource => {
                    this.getMockupList(resource.id.value);
                });
            });
        },
        getMockupList(productId) {
            Nova.request().get('/nova-api/mockups?viaResource=products&viaResourceId=' + productId + '&viaRelationship=mockups&relationshipType=hasMany')
                .then(
                    (response) => {
                        let list = [];
                        let images = [];
                        response.data.resources.forEach(resource => {
                            list.push({id: resource.id.value, title: resource.fields.find(x => x.attribute === 'title').value});
                            images.push({id: resource.id.value, title: resource.fields.find(x => x.component === 'media-library-field').value.preview});
                        });
                        this.mockupList.push({mainId: productId, items: list});
                        this.mockupImages.push({mainId: productId, items: images});
                    },
                    (error) => {
                        console.log(error);
                    });
        },
        setMockup(event, productId, index) {
            if (event === null) {
                delete this.productImages[index];
                this.$forceUpdate();
                return;
            }
            this.setMockupImage(productId, event.id, index)
        },
        setMockupImage(productId, mockupId, index) {
            let mockupImages = this.mockupImages.find(x => x.mainId === productId).items;
            this.productImages[index] = mockupImages.find(x => x.id === mockupId).title;
            this.$forceUpdate();
        },
        submitForm() {
            this.disabledButton = true;
            let formData = new FormData();
            formData.append('mockups', this.selectedMockups.map(x => x !== null ? x.id : null).filter(function (el) {return el != null;}));
            const config = {
                headers:{'Content-Type' : 'multipart/form-data'}
            };
            Nova.request().post('/nova-vendor/laravel-magento-variant-generator/create', formData, config)
                .then(response => {
                    if (response.data.status === 200) {
                        this.$toasted.show('Step 2: Add patterns', { type: 'success' });
                        this.$router.push('/laravel-magento-variant-generator-upload/'+response.data.id);
                    }
            });
        }
    }
}
</script>

<style>
/* Scoped Styles */
</style>
