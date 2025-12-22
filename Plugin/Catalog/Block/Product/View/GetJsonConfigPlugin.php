<?php
declare(strict_types=1);

namespace Hmh\SpCountDown\Plugin\Catalog\Block\Product\View;

use Hmh\SpCountDown\Model\Config\ConfigProvider;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Framework\Serialize\Serializer\Json;

class GetJsonConfigPlugin
{
    public function __construct(
        private readonly ConfigProvider $configProvider,
        private readonly Json $jsonSerializer
    ) {
    }

    /**
     * Append special price end date to product JSON config.
     */
    public function afterGetJsonConfig(View $subject, $result): string
    {
        if (!$this->configProvider->isEnabled((int)$subject->getStoreId())) {
            return $result;
        }
        try {
            $config = $this->jsonSerializer->unserialize($result);
        } catch (\InvalidArgumentException $exception) {
            return $result;
        }
        $product = $subject->getProduct();
        if (!$product) {
            return $result;
        }
        if ($this->isCountDownApplicable($product)) {
            $config['special_to_date'] = $product->getSpecialToDate();
        }
        if ($product->getTypeId() === ConfigurableType::TYPE_CODE) {
            $config = $this->appendConfigurableChildrenDates($product, $config);
        }

        return $this->jsonSerializer->serialize($config);
    }

    /**
     * Check if product count down is applicable
     */
    protected function isCountDownApplicable(Product $product): bool
    {
        $specialToDate = $product->getSpecialToDate();
        if (!$specialToDate) {
            return false;
        }
        $specialToTimestamp = strtotime($specialToDate);
        if ($specialToTimestamp === false) {
            return false;
        }
        // Normalize to the end of the special-to date.
        $specialToTimestamp = strtotime(date('Y-m-d 23:59:59', $specialToTimestamp));
        $secondsUntilEnd = $specialToTimestamp - time();
        if ($secondsUntilEnd < 0) {
            return false;
        }
        $countdownDays = $this->configProvider->getCountdownDays((int)$product->getStoreId());
        $countdownWindowSeconds = $countdownDays * 24 * 60 * 60;

        return $secondsUntilEnd <= $countdownWindowSeconds;
    }

    /**
     * Add special to date on child for configurable product
     */
    protected function appendConfigurableChildrenDates(Product $product, array $config): array
    {
        $childProducts = $product->getTypeInstance()->getUsedProducts($product);
        foreach ($childProducts as $childProduct) {
            if (!$childProduct instanceof Product) {
                continue;
            }
            $childProduct->setStoreId((int)$product->getStoreId());
            if (!$this->isCountDownApplicable($childProduct)) {
                continue;
            }
            $config['optionSpecialEndDate'][$childProduct->getId()]['special_to_date'] = $childProduct->getSpecialToDate();
        }

        return $config;
    }
}
