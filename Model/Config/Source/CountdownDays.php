<?php
declare(strict_types=1);

namespace Hmh\SpCountDown\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CountdownDays implements OptionSourceInterface
{
    /**
     * Return options for countdown window (1-20 days).
     */
    public function toOptionArray(): array
    {
        return array_map(
            static fn (int $day): array => [
                'value' => $day,
                'label' => (string) $day,
            ],
            range(1, 20)
        );
    }
}
