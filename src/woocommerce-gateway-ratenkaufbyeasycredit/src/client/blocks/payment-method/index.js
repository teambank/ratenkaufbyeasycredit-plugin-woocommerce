import { ExpressPaymentMethodConfiguration } from '@woocommerce/blocks-registry';

const methodConfiguration = {
    name: 'easycredit-Ratenkauf',
    content: <div>easycredit-express-button</div>,
    edit: <div>easycredit-express-button</div>,
    canMakePayment: () => true,
    paymentMethodId: 'easycredit',
    //supports: {
    //    features: [ ''],
    //},
    label: <div>easycredit-checkout-label</div>,
    ariaLabel: 'easycredit'
}

export default methodConfiguration