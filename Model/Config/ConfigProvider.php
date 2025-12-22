<?php
declare(strict_types=1);

namespace Hmh\SpCountDown\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider
{
    private const XML_PATH_ENABLED = 'hmh_spcountdown/general/enabled';
    private const XML_PATH_COUNTDOWN_DAYS = 'hmh_spcountdown/general/countdown_days';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {}

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getCountdownDays(?int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_COUNTDOWN_DAYS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
