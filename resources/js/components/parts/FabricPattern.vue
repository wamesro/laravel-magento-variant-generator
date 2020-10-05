<template>
    <card
        class="bg-50 place-pattern-card"
    >
        <div class="place-pattern-card-title">
            <h4>{{ mockup.title }}</h4>
        </div>
        <div class="fabric-library-box">
            <canvas
                class="fabric-library-box-canvas" :id="'canvas'+mockup.id" width="566" height="566"

            ></canvas>
        </div>
        <v-select
            class="place-pattern-card-select"
            :options="['Canada', 'United States']"
        ></v-select>
    </card>
</template>

<script>
    import { fabric } from 'fabric';
    import vSelect from 'vue-select';

    export default {
        components: {
            vSelect
        },
        props: {
            mockup: Array
        },
        mounted() {
            this.addCanvas();
        },
        methods: {
            changePosition(event) {
                const formData = new FormData();

                let object = event.target;
                formData.append('mockup_id', this.mockup.mockup_id);
                formData.append('pattern_angle', object.angle);
                formData.append('pattern_left', object.left);
                formData.append('pattern_top', object.top);
                formData.append('pattern_scaleX', object.scaleX);
                formData.append('pattern_scaleY', object.scaleY);
                const config = {
                    headers:{'Content-Type' : 'multipart/form-data'}
                };
                Nova.request()
                    .post('/nova-vendor/laravel-magento-variant-generator/'+this.$route.params.id+'/variants/set/pattern_position',
                        formData,
                        config)
                    .then(response => {
                        console.log(response)
                    });
            },
            addCanvas() {
                const canvas = new fabric.Canvas('canvas'+this.mockup.id);

                window.fabric.Image.fromURL(this.mockup.background, (img) => {
                    canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                        scaleX: canvas.width / img.width,
                        scaleY: canvas.height / img.height
                    });
                });

                fabric.Image.fromURL(this.mockup.pattern, function(myImg) {
                    let img1 = myImg.set({
                        left: 0,
                        top: 0,
                        scaleX: 150,
                        scaleY: 150,
                        centeredScaling: true
                    });
                    img1.scaleToWidth(300);
                    canvas.add(img1);
                    img1.setControlsVisibility({mt: false, mb: false, ml: false, mr: false,});
                });

                canvas.on('object:scaling', function (event) {
                    let scaledObject = event.target;
                    if(scaledObject.flipX === true || scaledObject.flipY === true){
                        scaledObject.flipX = false;
                        scaledObject.flipY = false;
                    }
                });

                canvas.on('object:modified', (e) => this.changePosition(e));
            }
        }
    }
</script>

<style lang="scss" scoped>
    .fabric-library-box {
        width: 100%;
        border: 1px solid red;
        height: 100%;
        &-canvas {
            width: 100%;
            height: 100%;
        }
    }

    .place-pattern-card {
        width: 600px;
        height: 600px;
        padding: 1em;
        position: relative;
        margin: 1em 1em 5em 1em;
    }

    .place-pattern-card-title {
        position: absolute;
        top: -20px;
    }

    .place-pattern-card-select {
        position: absolute;
        bottom: -45px;
        width: 100%;
        left: 0;
    }
</style>
