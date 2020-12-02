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
            label="title"
            :options="siblings"
            v-model="selectBox"
        ></v-select>
        <vue-range-slider class="range-slider" ref="slider" v-on:drag-end="changeOpacity" v-model="opacity"></vue-range-slider>
    </card>
</template>

<script>
    import { fabric } from 'fabric';
    import vSelect from 'vue-select';
    import 'vue-range-component/dist/vue-range-slider.css'
    import VueRangeSlider from 'vue-range-component'

    export default {
        components: {
            vSelect,
            VueRangeSlider
        },
        data() {
            return {
                siblings: [],
                selectBox: [],
                canvas: [],
                opacity: 100
            }
        },
        props: {
            mockup: Array,
        },
        watch: {
            selectBox: function (value) {
                if (value === null) {
                    this.selectBox = this.siblings.find(x => x.id === this.mockup.id);
                    return
                }
                this.setImage(value.pattern, value.background);
            }
        },
        created() {

        },
        mounted() {
            this.canvas = new fabric.Canvas('canvas'+this.mockup.id);
            this.addCanvas();
            this.getSiblings();
        },
        methods: {
            changePosition(event) {
                let object = event.target;

                if (object === null) {
                    return false;
                }

                const formData = new FormData();

                formData.append('mockup_id', this.mockup.mockup_id);
                formData.append('angle', object.angle);
                formData.append('left', object.left);
                formData.append('top', object.top);
                formData.append('scaleX', object.getScaledWidth());
                formData.append('scaleY', object.getScaledHeight());
                formData.append('lineCoords_bl', object.lineCoords.bl);
                formData.append('lineCoords_br', object.lineCoords.br);
                formData.append('lineCoords_tl', object.lineCoords.tl);
                formData.append('lineCoords_tr', object.lineCoords.tr);
                formData.append('opacity', this.opacity / 100);

                object.set({
                    opacity: this.opacity/100
                });

                this.mockup.position = JSON.stringify({
                    opacity: this.opacity / 100,
                    angle: object.angle,
                    left: object.left,
                    top: object.top,
                    scaleX: object.getScaledWidth(),
                    scaleY: object.getScaledHeight(),
                    lineCoords_bl: object.lineCoords.bl,
                    lineCoords_br: object.lineCoords.br,
                    lineCoords_tl: object.lineCoords.tl,
                    lineCoords_tr: object.lineCoords.tr,
                });

                const config = {
                    headers:{'Content-Type' : 'multipart/form-data'}
                };
                Nova.request()
                    .post('/nova-vendor/laravel-magento-variant-generator/'+this.$route.params.id+'/variants/set/pattern_position',
                        formData,
                        config)
                    .then(response => {
                    });
            },
            addCanvas() {
                this.setImage(this.mockup.pattern, this.mockup.background);
            },
            setImage(imgLink, bgLink) {
                let canvas = this.canvas;
                window.fabric.Image.fromURL(bgLink, (img) => {
                    canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                        scaleX: canvas.width / img.width,
                        scaleY: canvas.height / img.height
                    });
                });
                let self = this;
                fabric.Image.fromURL(imgLink, function(myImg) {
                    if (self.mockup.position === null) {
                        let img1 = myImg.set({
                            left: 0,
                            top: 0,
                            centeredScaling: true,
                            opacity: 1
                        });
                        img1.scaleToWidth(300);
                        img1.setControlsVisibility({mt: false, mb: false, ml: false, mr: false,});

                        let objs = canvas.getObjects();
                        if (objs.length) {
                            objs.forEach(function(e) {
                                if (e && e.type === 'image') {
                                    e._element.src = imgLink;
                                    canvas.renderAll();
                                }
                            });
                        } else canvas.add(img1);
                    } else  {
                        let patternPosition = JSON.parse(self.mockup.position);
                        self.opacity = patternPosition.opacity*100;
                        let img1 = myImg.set({
                            left: parseInt(patternPosition.left),
                            top: parseInt(patternPosition.top),
                            angle: parseInt(patternPosition.angle),
                            opacity: parseFloat(patternPosition.opacity),
                            centeredScaling: true
                        });
                        img1.scaleToWidth(parseInt(patternPosition.scaleX));
                        img1.setControlsVisibility({mt: false, mb: false, ml: false, mr: false,});

                        let objs = canvas.getObjects();
                        if (objs.length) {
                            objs.forEach(function(e) {
                                if (e && e.type === 'image') {
                                    e._element.src = imgLink;
                                    canvas.renderAll();
                                }
                            });
                        } else canvas.add(img1);
                    }
                });

                canvas.on('object:scaling', function (event) {
                    let scaledObject = event.target;
                    if(scaledObject.flipX === true || scaledObject.flipY === true){
                        scaledObject.flipX = false;
                        scaledObject.flipY = false;
                    }
                });
                canvas.on('mouse:up', (e) => this.changePosition(e));
                canvas.on('object:modified', (e) => this.changePosition(e));
            },
            getSiblings() {
                Nova.request()
                    .get('/nova-vendor/laravel-magento-variant-generator/' + this.mockup.id +'/get/siblings')
                    .then(response => {
                        this.siblings = response.data;
                        this.selectBox = this.siblings.find(x => x.id === this.mockup.id);
                    });
            },
            changeOpacity() {
                let canvas = this.canvas;
                let obj = canvas.getObjects()[0];
                obj.set({
                    opacity: this.opacity/100
                });
                canvas.renderAll();

                const formData = new FormData();
                formData.append('mockup_id', this.mockup.mockup_id);
                formData.append('angle', obj.angle);
                formData.append('left', obj.left);
                formData.append('top', obj.top);
                formData.append('scaleX', obj.getScaledWidth());
                formData.append('scaleY', obj.getScaledHeight());
                formData.append('lineCoords_bl', obj.lineCoords.bl);
                formData.append('lineCoords_br', obj.lineCoords.br);
                formData.append('lineCoords_tl', obj.lineCoords.tl);
                formData.append('lineCoords_tr', obj.lineCoords.tr);
                formData.append('opacity', this.opacity/100);

                const config = {
                    headers:{'Content-Type' : 'multipart/form-data'}
                };
                Nova.request()
                    .post('/nova-vendor/laravel-magento-variant-generator/'+this.$route.params.id+'/variants/set/pattern_position',
                        formData,
                        config)
                    .then(response => {
                    });
            }
        },
    }
</script>

<style lang="scss" scoped>
    .fabric-library-box {
        width: 100%;
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
    /deep/ .range-slider {
        padding: 3px !important;
    }
</style>
