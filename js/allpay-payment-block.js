
    if (window.wc && window.wc.wcBlocksRegistry && window.React && window.wp) {
        const { registerPaymentMethod } = window.wc.wcBlocksRegistry;

        const { createElement } = React;
        const { __ } = wp.i18n;
        const { getSetting } = wc.wcSettings;
        const { decodeEntities } = wp.htmlEntities;

        const PAYMENT_METHOD_NAME = 'allpay-payment-gateway';
        // const settings = getSetting(PAYMENT_METHOD_NAME, {});
        const settings = wc.wcSettings.allSettings.paymentMethodData[PAYMENT_METHOD_NAME];

        const label = decodeEntities(settings.title || 'Credit card');
        const description = decodeEntities(settings.description || 'Pay securely using your credit card');

        // Content component for description
        const Content = () => createElement('div', null, description);

        // Label component for title
        const Label = (props) => {
            const { PaymentMethodLabel } = props.components;
            return createElement(PaymentMethodLabel, { text: label });
        };

        registerPaymentMethod ({
            name: PAYMENT_METHOD_NAME,
            savedTokenComponent: createElement(Content),
            label: createElement(Label, { components: { PaymentMethodLabel: (props) => createElement('span', null, props.text) }}), 
            content: createElement(Content),
            edit: createElement(Content),
            canMakePayment: (order) => { return true; },
            ariaLabel: label,
            supports: {
                features: settings.supports || ['products'], 
            },
        });
    }

