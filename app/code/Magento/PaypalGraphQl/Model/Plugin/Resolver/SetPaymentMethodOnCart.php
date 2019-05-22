<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Plugin\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Paypal\Model\Express\Checkout\Factory as CheckoutFactory;
use Magento\PaypalGraphQl\Model\PaypalExpressAdditionalDataProvider;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\PaypalGraphQl\Model\Provider\Checkout as CheckoutProvider;
use Magento\PaypalGraphQl\Model\Provider\Config as ConfigProvider;

class SetPaymentMethodOnCart
{
    private const PATH_CODE = 'input/payment_method/code';

    private $allowedPaymentMethodCodes = [];

    /**
     * @var CheckoutFactory
     */
    private $checkoutFactory;

    /**
     * @var PaypalExpressAdditionalDataProvider
     */
    private $paypalExpressAdditionalDataProvider;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var CheckoutProvider
     */
    private $checkoutProvider;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @param CheckoutFactory $checkoutFactory
     * @param PaypalExpressAdditionalDataProvider $paypalExpressAdditionalDataProvider
     * @param ArrayManager $arrayManager
     * @param CheckoutProvider $checkoutProvider
     */
    public function __construct(
        CheckoutFactory $checkoutFactory,
        PaypalExpressAdditionalDataProvider $paypalExpressAdditionalDataProvider,
        ArrayManager $arrayManager,
        CheckoutProvider $checkoutProvider,
        ConfigProvider $configProvider,
        array $allowedPaymentMethodCodes = []
    ) {
        $this->checkoutFactory = $checkoutFactory;
        $this->paypalExpressAdditionalDataProvider = $paypalExpressAdditionalDataProvider;
        $this->arrayManager = $arrayManager;
        $this->checkoutProvider = $checkoutProvider;
        $this->configProvider = $configProvider;
        $this->allowedPaymentMethodCodes = $allowedPaymentMethodCodes;
    }

    /**
     * Update Paypal payment information on cart
     *
     * @param ResolverInterface $subject
     * @param $resolvedValue
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterResolve(
        ResolverInterface $subject,
        $resolvedValue,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $paymentCode = $this->arrayManager->get(self::PATH_CODE, $args) ?? '';
        if (!$this->isAllowedPaymentMethod($paymentCode)) {
            return $resolvedValue;
        }

        $paypalAdditionalData = $this->paypalExpressAdditionalDataProvider->getData($args);
        $payerId = $paypalAdditionalData[$paymentCode]['payer_id'] ?? null;
        $token = $paypalAdditionalData[$paymentCode]['token'] ?? null;
        $cart = $resolvedValue['cart']['model'];

        if ($payerId && $token) {
            $config = $this->configProvider->getConfig($paymentCode);
            $checkout = $this->checkoutProvider->getCheckout($config, $cart);

            try {
                $checkout->returnFromPaypal($token, $payerId);
            } catch (LocalizedException $e) {
                throw new GraphQlInputException(__($e->getMessage()));
            }
        }

        return $resolvedValue;
    }

    /**
     * Check if payment method code is one that should be handled by this plugin
     *
     * @param string $paymentCode
     * @return bool
     */
    private function isAllowedPaymentMethod(string $paymentCode): bool
    {
        return !empty($paymentCode) && in_array($paymentCode, $this->allowedPaymentMethodCodes);
    }
}
