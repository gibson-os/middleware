<?php
declare(strict_types=1);

namespace GibsonOS\Module\Middleware\Builder\Tibber;

use GibsonOS\Module\Middleware\Builder\Tibber\PriceRating\TypeQueryBuilder;

class PriceRatingQueryBuilder
{
    private ?ThresholdPercentagesQueryBuilder $thresholdPercentagesQueryBuilder = null;

    private ?TypeQueryBuilder $hourlyQueryBuilder = null;

    private ?TypeQueryBuilder $dailyQueryBuilder = null;

    private ?TypeQueryBuilder $monthlyQueryBuilder = null;

    public function build(): array
    {
        $query = [];

        if ($this->thresholdPercentagesQueryBuilder instanceof ThresholdPercentagesQueryBuilder) {
            $query['thresholdPercentages'] = $this->thresholdPercentagesQueryBuilder->build();
        }

        if ($this->hourlyQueryBuilder instanceof TypeQueryBuilder) {
            $query['hourly'] = $this->hourlyQueryBuilder->build();
        }

        if ($this->dailyQueryBuilder instanceof TypeQueryBuilder) {
            $query['daily'] = $this->dailyQueryBuilder->build();
        }

        if ($this->monthlyQueryBuilder instanceof TypeQueryBuilder) {
            $query['monthly'] = $this->monthlyQueryBuilder->build();
        }

        return $query;
    }

    public function withThresholdPercentagesQueryBuilder(ThresholdPercentagesQueryBuilder $thresholdPercentagesQueryBuilder): PriceRatingQueryBuilder
    {
        $this->thresholdPercentagesQueryBuilder = $thresholdPercentagesQueryBuilder;

        return $this;
    }

    public function withHourlyQueryBuilder(TypeQueryBuilder $hourlyQueryBuilder): PriceRatingQueryBuilder
    {
        $this->hourlyQueryBuilder = $hourlyQueryBuilder;

        return $this;
    }

    public function withDailyQueryBuilder(TypeQueryBuilder $dailyQueryBuilder): PriceRatingQueryBuilder
    {
        $this->dailyQueryBuilder = $dailyQueryBuilder;

        return $this;
    }

    public function withMonthlyQueryBuilder(TypeQueryBuilder $monthlyQueryBuilder): PriceRatingQueryBuilder
    {
        $this->monthlyQueryBuilder = $monthlyQueryBuilder;

        return $this;
    }
}
