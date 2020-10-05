Nova.booting((Vue, router, store) => {
    router.addRoutes([
        {
          name: 'laravel-magento-variant-generator',
          path: '/laravel-magento-variant-generator',
          component: require('./components/Tool'),
        },
        {
            name: 'laravel-magento-variant-generator',
            path: '/laravel-magento-variant-generator-upload/:id',
            component: require('./components/Upload'),
        },
        {
            name: 'laravel-magento-variant-generator',
            path: '/laravel-magento-variant-generator-variants/:id',
            component: require('./components/Variants'),
        },
        {
            name: 'laravel-magento-variant-generator',
            path: '/laravel-magento-variant-generator-place-patterns/:id',
            component: require('./components/PlacePatterns'),
        },
    ])
})
