<template>
    <div class="laravel-magento-upload">
        <input type="file" ref="file" multiple="multiple" @change="selectFile">
        <button 
            class="btn btn-default btn-primary"
            style="cursor: pointer;"
            :disabled="buttonDisabled"
            @click="submitForm"
        >{{ __('Varianty') }}</button>
    </div>
</template>

<script>
    export default {
        name: "Upload",
        props: {
          mockups: Array
        },
        data: () => ({
            patterns: [],
            description: '',
            productId: 0,
            buttonDisabled: false
        }),
        methods: {
            selectFile(event) {
                this.patterns = event.target.files;
            },
            submitForm() {
                this.buttonDisabled = true;

                let formData = new FormData();
                for( var i = 0; i < this.$refs.file.files.length; i++ ){
                    let file = this.$refs.file.files[i];
                    formData.append('patterns[' + i + ']', file);
                }
                formData.append('mockups', this.mockups.map(x => x !== null ? x.id : null).filter(function (el) {return el != null;}));

                const config = {
                    headers:{'Content-Type' : 'multipart/form-data'}
                };

                Nova.request().post('/nova-vendor/laravel-magento-variant-generator/create', formData, config)
                .then(response => {
                    console.log(response.data);
                });
            }
        }
    }
</script>
