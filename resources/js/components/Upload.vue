<template>
    <div>
        <heading class="mb-6">Laravel Magento Variant Generator</heading>

        <div class="flex" style="">
            <div class="relative h-9 flex-no-shrink mb-6">
            </div>
            <div class="w-full flex items-center mb-6"><div class="flex w-full justify-end items-center mx-3"></div> <div class="flex-no-shrink ml-auto">
                <button class="btn btn-default btn-primary"
                        :disabled="true">
                    {{ __('Variants') }}
                </button>
            </div>
            </div>
        </div>
        <card
            class="flex flex-row"
            style="min-height: 300px;padding: 2em;"
        >
            <vue-dropzone
                ref="myVueDropzone"
                id="dropzone"
                :options="dropzoneOptions"
                :useCustomSlot=true
                v-on:vdropzone-success="attachPattern">
                <div class="dropzone-custom-content">
                    <h3 class="dropzone-custom-title">{{ __('Presuňte sem súbory myšou') }}</h3>
                    <div class="subtitle">...{{ __('alebo kliknite a vyberte súbory z počítača') }}</div>
                </div>
            </vue-dropzone>
        </card>
    </div>
</template>

<script>
    import vue2Dropzone from 'vue2-dropzone'
    import 'vue2-dropzone/dist/vue2Dropzone.min.css'

    export default {
        components: {
            vueDropzone: vue2Dropzone
        },
        data: () => ({
            dropzoneOptions: {
                url: "https://httpbin.org/post",
                thumbnailWidth: 150,
                maxFilesize: 0.5,
                addRemoveLinks: true,
                headers: {'Content-Type' : 'multipart/form-data'}
            },
            configurationItemOptions: {
                id: null,
                mockup_ids: [],
                patterns: [],
                status: null
            }
        }),
        mounted() {
            this.getConfiguratorOptions();
        },
        methods: {
            getConfiguratorOptions() {
                Nova.request()
                    .get('/nova-vendor/laravel-magento-variant-generator/get/' + this.$route.params.id)
                    .then(response => {
                        this.configurationItemOptions.id = response.data.data.id;
                        this.configurationItemOptions.mockup_ids = response.data.data.mockup_ids.split(',');
                        this.configurationItemOptions.patterns = response.data.data.patterns;
                        this.configurationItemOptions.status = response.data.data.status;
                        console.log(this.configurationItemOptions);
                    });
            },
            attachPattern(file, response) {
                const formData = new FormData();
                formData.append('file', file);
                const config = {
                    headers:{'Content-Type' : 'multipart/form-data'}
                };
                Nova.request()
                    .post('/nova-vendor/laravel-magento-variant-generator/'+ this.configurationItemOptions.id +'/patterns/append',
                        formData,
                        config)
                    .then(response => {
                        console.log(response.data);
                    });
            }
        }
    }
</script>

<style lang="scss" scoped>
    #dropzone {
        width: 100%;
        flex-direction: row;
        align-items: center;
        justify-content: center;
    }
    .dropzone-custom-content {
        text-align: center;
    }

    .dropzone-custom-title {
        margin-top: 0;
        color: var(--primary);
    }

    .subtitle {
        color: #314b5f;
    }
</style>
