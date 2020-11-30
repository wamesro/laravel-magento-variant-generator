<template>
    <div>
        <div class="flex" style="">
            <div class="relative h-9 flex-no-shrink mb-6">
                <h1 class="text-90 font-normal text-2xl">
                    <button @click="goBack()" class="no-underline text-primary font-bold dim router-link-active" >←</button>
                    <span class="px-2 text-70">/</span> {{ __('Place Patterns') }}
                </h1>
            </div>
            <div class="w-full flex items-center mb-6"><div class="flex w-full justify-end items-center mx-3"></div> <div class="flex-no-shrink ml-auto">
                <button class="btn btn-default btn-primary" @click="setMainProduct()">{{ __('Generate') }}</button>
            </div>
            </div>
        </div>
        <card class="languages-select" >
            <Skeleton v-if="!mainProduct.title" width="3em" height="2em" count="4"/>
            <div class="input" v-for="(language, index) in languages" :key="language" v-if="mainProduct.title">
                <input
                    type="checkbox"
                    class="checkbox"
                    :checked="selectedLanguages[language]"
                    v-model="selectedLanguages[language]"
                >
                <span class="text-uppercase">
                    {{ language.toUpperCase() }}
                </span>
            </div>
        </card>
        <hr>
        <card class="place-pattern-card-wrapper">
            <Skeleton v-if="!mainProduct.title" width="100%" height="5em" count="4"/>
            <div class="language" v-for="(language, index) in languages" :key="language" v-if="mainProduct.title">
                <card class="main-product-languages" v-if="selectedLanguages[language]">
                    <div class="card-checkbox">
                        <input
                            type="checkbox"
                            class="checkbox"
                            :checked="selectedLanguages[language]"
                            v-model="selectedLanguages[language]"
                        >
                    </div>
                    <div class="card-content">
                        <h3>{{ __('Main Product Title') }} {{ language }}</h3>
                        <input type="text" v-model="mainProduct.title[language]" :placeholder="__('Main Product Title')" class="form-control form-input form-input-bordered">
                        <div class="mt-3">
                            <h3>{{ __('Main Product Description') }} {{ language }}</h3>
                            <trix-editor
                                :ref="'mainDesctiptionEditor' + language"
                                @keydown.stop
                                :value="mainProduct.mainDescription[language]"
                                :placeholder="__('Main Product Description') + ' in ' + language"
                                class="trix-content"
                                @trix-change="handleChange('mainDesctiptionEditor' + language, mainProduct.mainDescription[language])"
                                @trix-initialize="initialize('mainDesctiptionEditor' + language, mainProduct.mainDescription[language])"
                                @trix-attachment-add="handleAddFile"
                                @trix-attachment-remove="handleRemoveFile"
                                @trix-file-accept="handleFileAccept"
                            />
                        </div>
                        <div class="mt-3">
                            <h3>{{ __('Variants Description') }} {{ language }}</h3>
                            <trix-editor
                                :ref="'variantDescriptionEditor' + language"
                                @keydown.stop
                                :value="mainProduct.variantDescription[language]"
                                :placeholder="__('Variants Description') + ' in ' + language"
                                class="trix-content"
                                @trix-change="handleChange('variantDescriptionEditor' + language, mainProduct.variantDescription[language])"
                                @trix-initialize="initialize('variantDescriptionEditor' + language, mainProduct.variantDescription[language])"
                                @trix-attachment-add="handleAddFile"
                                @trix-attachment-remove="handleRemoveFile"
                                @trix-file-accept="handleFileAccept"
                            />
                        </div>
                    </div>
                </card>
                <hr>
            </div>
            <h3 class="mt-2 mb-2">{{ __('Main product preview image') }}</h3>

            <Skeleton v-if="!products[0]" height="2em" count="1"/>
            <Skeleton v-if="!products[0]" width="200px" height="200px" count="1"/>
            <v-select
                v-if="products[0]"
                style="display: block"
                label="title"
                index="id"
                :placeholder="__('Select variant')"
                :options="products"
                v-model="mainVariantId"
                :value="products[0]"
            ></v-select>
            <div class="card" v-if="selectedVariant.img && products[0]" style="width: 200px; height: 200px">
                <img :src="selectedVariant.img">
            </div>
            <hr>
            <h3>{{ __('Additional main product images') }}</h3>
            <vue-dropzone
                ref="myVueDropzone"
                id="dropzone"
                :options="dropzoneOptions"
                :useCustomSlot=true
                v-on:vdropzone-success="attachPattern"
                v-on:vdropzone-removed-file="removePattern">
                <div class="dropzone-custom-content" v-if="!uploadInitLoading">
                    <h3 class="dropzone-custom-title">{{ __('Presuňte sem súbory myšou') }}</h3>
                    <div class="subtitle">...{{ __('alebo kliknite a vyberte súbory z počítača') }}</div>
                </div>
                <div class="dropzone-custom-content" v-if="uploadInitLoading">
                    <h3 class="dropzone-custom-title">{{ __('Počkajte prosím...') }}</h3>
                </div>
            </vue-dropzone>
            <h3 class="mt-3 mb-2">{{ __(':hover effect') }}</h3>
            <vue-dropzone
                ref="myVueDropzone2"
                id="hovereffect"
                :options="dropzone2Options"
                :useCustomSlot=true
                v-on:vdropzone-success="console.log('ok')"
                v-on:vdropzone-removed-file="console.log('ok')">
                <div class="dropzone-custom-content" v-if="!uploadInitLoading">
                    <h3 class="dropzone-custom-title">{{ __('Presuňte sem súbor myšou') }}</h3>
                    <div class="subtitle">...{{ __('alebo kliknite a vyberte súbor z počítača v prípade, že chcete pridať hover efekt.') }}</div>
                </div>
                <div class="dropzone-custom-content" v-if="uploadInitLoading">
                    <h3 class="dropzone-custom-title">{{ __('Počkajte prosím...') }}</h3>
                </div>
            </vue-dropzone>
        </card>
        <hr>
        <div class="flex" style="">
            <div class="relative h-9 flex-no-shrink mb-6">
            </div>
            <div class="w-full flex items-center mb-6"><div class="flex w-full justify-end items-center mx-3"></div> <div class="flex-no-shrink ml-auto">
                <button class="btn btn-default btn-primary" @click="setMainProduct()">{{ __('Generate') }}</button>
            </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { Skeleton } from 'vue-loading-skeleton';
    import vSelect from 'vue-select';
    import 'vue-select/dist/vue-select.css';

    import vue2Dropzone from 'vue2-dropzone'
    import 'vue2-dropzone/dist/vue2Dropzone.min.css'

    export default {
        components: {
            Skeleton,
            vSelect,
            vueDropzone: vue2Dropzone
        },
        data: () => ({
            uploadInitLoading: true,
            dropzoneOptions: {
                url: "https://httpbin.org/post",
                thumbnailWidth: 150,
                addRemoveLinks: true,
                headers: {'Content-Type' : 'multipart/form-data'}
            },
            dropzone2Options: {
                url: "https://httpbin.org/post",
                thumbnailWidth: 150,
                addRemoveLinks: true,
                headers: {'Content-Type' : 'multipart/form-data'},
                maxFiles: 1
            },
            mainProduct: [],
            mainVariantId: [],
            variants: [],
            products: [],
            disableRemove: false,
            languages: ['sk', 'cz', 'hu', 'ro'],
            selectedLanguages: {
                sk: true,
                cz: false,
                hu: false,
                ro: false,
            },
            selectedVariant: []
        }),
        mounted() {
            this.getVariants()
        },
        methods: {
            initialize(ref, value) {
                this.$refs[ref][0].editor.insertHTML(value)

                if (this.disabled) {
                    this.$refs[ref][0].setAttribute('contenteditable', false)
                }
            },

            handleChange(ref, value) {
                this.$emit('change', value)
            },

            handleFileAccept(e) {
                if (!this.withFiles) {
                    e.preventDefault()
                }
            },

            handleAddFile(event) {
                this.$emit('file-add', event)
            },

            handleRemoveFile(event) {
                this.$emit('file-remove', event)
            },
            setMainProduct() {
                this.disableRemove = true;
                let formData = new FormData();
                formData.append('main_configurator_item_variant_id', this.mainVariantId.variant_id);

                this.languages.forEach(language => {
                    if (!this.selectedLanguages[language]) return;
                    this.mainProduct.title[language] = this.mainProduct.title[language] ? this.mainProduct.title[language] : null;
                    formData.append('main_configurator_item_title_' + language, this.mainProduct.title[language]);
                    formData.append('main_configurator_item_description_' + language, this.$refs['mainDesctiptionEditor' + language][0].value);
                    formData.append('configurator_item_variants_description_' + language, this.$refs['variantDescriptionEditor' + language][0].value);
                });
                const config = {
                    headers:{'Content-Type' : 'multipart/form-data'}
                };
                Nova.request().post('/nova-vendor/laravel-magento-variant-generator/' + this.$route.params.id + '/set/main', formData, config)
                    .then(response => {
                        this.$router.push('/laravel-magento-variant-generator-generate/'+this.$route.params.id);
                });

            },
            goBack() {
                this.disableRemove = true;
                this.$router.push('/laravel-magento-variant-generator-place-patterns/'+ this.$route.params.id);
            },
            attachPattern(file, response) {
                const formData = new FormData();
                formData.append('file', file);
                const config = {
                    headers:{'Content-Type' : 'multipart/form-data'}
                };
                Nova.request()
                    .post('/nova-vendor/laravel-magento-variant-generator/'+ this.$route.params.id +'/setimages/main',
                        formData,
                        config)
                    .then(response => {

                    });
            },
            removePattern(file, error, xhr) {
                if (this.disableRemove === true) return;
                Nova.request()
                    .post('/nova-vendor/laravel-magento-variant-generator/'+ this.$route.params.id +'/deleteimages/main',
                        {
                            filename: file.name
                        })
                    .then(response => {
                        this.$toasted.show('File removed', { type: 'success' });
                    });
            },
            getPatterns() {
                Nova.request()
                    .get('/nova-vendor/laravel-magento-variant-generator/' + this.$route.params.id +'/getimages/main')
                    .then(response => {
                        response.data.forEach(data => {
                            let file = { size: data.size, name: data.name, type: data.mimetype };
                            let url = data.url;
                            this.$refs.myVueDropzone.manuallyAddFile(file, url);
                        });
                        this.$refs.myVueDropzone.enable();
                        this.uploadInitLoading = false;
                    });
            },
            getMain() {
                Nova.request()
                    .get('/nova-vendor/laravel-magento-variant-generator/get/' + this.$route.params.id)
                    .then(response => {
                        this.mainTitle = response.data.data.main_configurator_item_title;
                        this.mainVariantId = this.products[this.products.findIndex(current => {
                            if (parseInt(current.variant_id) === parseInt(response.data.data.main_configurator_item_variant_id)) {
                                return true;
                            }
                        })];

                        let title = JSON.parse(response.data.data.main_configurator_item_title);
                        this.languages.forEach(x => {
                            if (title[x]) {
                                this.selectedLanguages[x] = true;
                            }
                        });
                        this.mainProduct.title = title;
                        this.mainProduct.mainDescription = JSON.parse(response.data.data.main_configurator_item_description);
                        this.mainProduct.variantDescription = JSON.parse(response.data.data.configurator_item_variants_description);
                    });
            },
            getVariants() {
                Nova.request()
                    .get('/nova-vendor/laravel-magento-variant-generator/' + this.$route.params.id + '/variants/get')
                    .then(response => {
                        this.variants = response.data.data;
                        this.getProducts();
                    });
            },
            getProducts() {
                Nova.request()
                    .get('/nova-vendor/laravel-magento-variant-generator/' + this.$route.params.id + '/variants/get/products')
                    .then(response => {
                        this.variants.forEach(variant => {
                            response.data.mockups.find(x => {
                                if (parseInt(x.mockup_id) === parseInt(variant.mockup_id)) {
                                    response.data.products.find(product => {
                                        if (parseInt(product.id) === parseInt(x.product_id)) {
                                            this.products.push({
                                                id: product.id,
                                                configurator_item_id: variant.configurator_item_id,
                                                variant_id: variant.id,
                                                title: product.title + ' ' + variant.color
                                            });
                                        }
                                    });
                                }
                            })
                        });
                        this.mainVariantId = this.products[0];
                        this.getPatterns();
                        this.getMain()
                    });
            }
        },
        watch: {
            mainVariantId: function (val) {
                if (!val) return null;
                this.variants.find(x => {
                    if (x.id === val.variant_id) {
                        this.selectedVariant.img = x.final_image;
                    }
                })
            }
        }
    }
</script>


<style lang="scss" scoped>
    .languages-select {
        padding: 2em;
        display: flex;
        flex-direction: row;
        .input {
            padding: 1em;
        }
        /deep/ .pu-skeleton {
            margin-right: 1em;
        }
    }
    .place-pattern-card-wrapper {
        min-height: 300px;
        padding: 2em;
        justify-content: center;
        display: flex;
        flex-wrap: wrap;
        flex-direction: column;
        input {
            width: 50%;
        }
        .v-select {
            width: 50%;
        }
        /deep/ .pu-skeleton {
            margin-top: 1em;
            margin-bottom: 1em;
            display: block;
        }
    }
    .language {
        width: 100%;
        .main-product-languages {
            width: 70%;
            display: flex;
            flex-direction: row;
            .card {
                &-checkbox {
                    input {
                        height: 100%;
                        width: 7em;
                    }
                }
                &-content {
                    width: 100%;
                    padding: 2em;
                    display: flex;
                    flex-direction: column;
                    textarea {
                        height: 10em;
                    }
                }
            }
        }
    }
    #dropzone {
        width: 100%;
        margin-top: 1em;
    }
</style>
