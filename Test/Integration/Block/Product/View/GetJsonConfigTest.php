<?php
declare(strict_types=1);

namespace Hmh\SpCountDown\Test\Integration\Block\Product\View;

use Magento\Catalog\Block\Product\View;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Registry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class GetJsonConfigTest extends TestCase
{
    /**
     * @magentoConfigFixture current_store hmh_spcountdown/general/enabled 1
     * @magentoConfigFixture current_store hmh_spcountdown/general/countdown_days 10
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetJsonConfigIncludesSpecialToDateWithinCountdownWindow(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $specialToDate = (new \DateTime('+5 days'))->format('Y-m-d 00:00:00');

        $storeManager = $objectManager->get(StoreManagerInterface::class);
        $store = $storeManager->getDefaultStoreView();
        $storeManager->setCurrentStore($store->getId());

        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple', false, null, true);
        $product->setStoreId((int)$store->getId());
        $product->setSpecialPrice(7);
        $product->setSpecialFromDate((new \DateTime())->format('Y-m-d 00:00:00'));
        $product->setSpecialToDate($specialToDate);
        $productRepository->save($product);

        $registry = $objectManager->get(Registry::class);
        $registry->register('product', $product);

        $block = $objectManager->create(View::class);
        $block->setData('store_id', (int)$store->getId());
        $config = json_decode($block->getJsonConfig(), true);
        $registry->unregister('product');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('special_to_date', $config);
        $this->assertSame($specialToDate, $config['special_to_date']);
    }
}
