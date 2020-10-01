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
    ])
})
