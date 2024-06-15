<?php

namespace App\Services;

use App\Services\Interfaces\DiscountConditionServiceInterface;
use App\Repositories\Interfaces\DiscountConditionRepositoryInterface;


class DiscountConditionService implements DiscountConditionServiceInterface
{
    private $conditionRepository;
    public function __construct(DiscountConditionRepositoryInterface $conditionRepository)
    {
        $this->conditionRepository = $conditionRepository;
    }
    public function limitTest($total, $discount)
    {
        $condition = $this->conditionRepository->first();

        if ($condition) {
            $maxDiscount = $total * ($condition->maximum / 100);

            if ($discount > $maxDiscount) {
                return $maxDiscount;
            }

            return true;
        }
        return false;
    }
}
