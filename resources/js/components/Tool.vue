<template>
    <div>
        <heading class="mb-6">Laravel Magento Variant Generator</heading>

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
        <hr>
        <card
            class="flex flex-col items-center justify-center"
            style="min-height: 300px;padding: 2em;"
        >
            <h3>{{ __('Tu nahrajte vzory') }}</h3>
            <br>
            <Upload :mockups="selectedMockups"/>
        </card>
    </div>
    </template>

<script>
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import Upload from './Upload';

export default {
    components: {
        vSelect,
        Upload
    },
    data() {
        return {
            productList: [],
            mockupList: [],
            mockupImages: [],
            productImages: [],
            selectedMockups: [],
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
        }
    }
}
</script>

<style>
/* Scoped Styles */
</style>
