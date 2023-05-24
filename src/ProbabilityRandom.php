<?php

namespace Jerry58321\ProbabilityRandom;

class ProbabilityRandom
{
    /**
     * 差異總值
     *
     * @var mixed
     */
    protected $diffTotal;

    /**
     * 最小值
     *
     * @var int
     */
    protected $min;

    /**
     * 最大值
     *
     * @var int
     */
    protected $max;

    /**
     * 區間比例
     *
     * @var array
     */
    protected $proportions;

    /**
     * 區間機率
     *
     * @var array
     */
    protected $probabilities;

    /**
     * @var bool
     */
    protected $useSafeRange;

    /**
     * 使用安全區間 (防止random function arg1 > arg2)
     * 區間過小時則不控制區間或機率
     */
    const USE_SAFE_RANGE = true;


    public function __construct(int $min, int $max, bool $useSafeRange)
    {
        $this->min = $min;
        $this->max = $max;
        $this->useSafeRange = $useSafeRange;
        $this->diffTotal = $max - $min;
    }

    /**
     * @param  int  $min
     * @param  int  $max
     * @param  bool  $useSafeRange
     * @return ProbabilityRandom
     */
    public static function build(int $min, int $max, bool $useSafeRange = self::USE_SAFE_RANGE): ProbabilityRandom
    {
        if ($min > $max) {
            throw new \LogicException('min must be less than or equal to max');
        }

        return new static($min, $max, $useSafeRange);
    }

    /**
     * 設置區間比例
     *
     * @param  array  $proportions
     * @return $this
     */
    public function setRangeProportions(array $proportions): ProbabilityRandom
    {
        $this->proportions = $proportions;

        return $this;
    }

    /**
     * 設置區間機率
     *
     * @param  array  $probabilities
     * @return $this
     */
    public function setRangeProbabilities(array $probabilities): ProbabilityRandom
    {
        $this->probabilities = $probabilities;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function random(): int
    {
        $this->checkRangeSettingLegal();
        $this->checkProbabilitiesSettingLegal();
        $actualRangeValue = $this->getActualRangeValue();

        // 不在安全範圍內則不使用機率控制
        if ($this->useSafeRange && !$this->isSafeRange()) {
            return random_int($this->min, $this->max);
        }

        // 無區間時不使用機率
        if (empty($this->proportions)) {
            return random_int($this->min, $this->max);
        }

        $probabilityRangeIndex = $this->getProbabilityRangeIndex();
        return random_int(...$actualRangeValue[$probabilityRangeIndex]);
    }

    /**
     * 取得實際區間數值
     *
     * @return array
     */
    public function getActualRangeValue(): array
    {
        $lastValue = 0;
        return collect($this->proportions)->map(function ($proportion, $index) use (&$lastValue) {
            if ($index === 0) {
                $value = $lastValue = floor($this->min + $this->diffTotal * $proportion);
                return [$this->min, $value];
            }

            $currentValue = floor($lastValue + ($this->diffTotal * $proportion));
            $range = [$lastValue + 1, $currentValue];
            $lastValue = $currentValue;
            return $range;
        })
            ->push([$lastValue + 1, $this->max])
            ->toArray();
    }

    /**
     * 取得機率區間位置
     *
     * @return mixed
     * @throws \Exception
     */
    protected function getProbabilityRangeIndex(): int
    {
        $randProbability = random_int(1, 100);

        return collect($this->probabilities)->filter(function ($probability) use (&$randProbability) {
            $randProbability -= ($probability * 100);
            return $randProbability <= 0;
        })->keys()->first();
    }

    /**
     * 取得期望值
     *
     * @return float
     */
    public function getExpectValue(): float
    {
        $actualRangeValue = $this->getActualRangeValue();

        if (($this->useSafeRange && !$this->isSafeRange()) || empty($this->proportions)) {
            return ($this->min + $this->max) / 2;
        }

        return collect($this->probabilities)->map(function ($probability, $index) use ($actualRangeValue) {
            [$min, $max] = $actualRangeValue[$index];
            return ($min + $max) / 2 * $probability;
        })->sum();
    }

    /**
     * 檢查區間設定是否合法
     *
     * @return true
     */
    public function checkRangeSettingLegal(): bool
    {
        if (empty($this->probabilities) && empty($this->proportions)) {
            return true;
        }

        if ((count($this->probabilities) - count($this->proportions)) !== 1) {
            throw new \LogicException('The setting of "probabilities" must be "proportions" quantity +1');
        }

        return true;
    }

    /**
     * 檢查機率設定是否合法
     *
     * @return true
     */
    public function checkProbabilitiesSettingLegal(): bool
    {
        $totalProbability = collect($this->probabilities)->map(function ($probability) {
            return bcmul($probability, 100);
        })->sum();

        if (!empty($this->probabilities) && $totalProbability != 100) {
            throw new \LogicException('sum of "probabilities" must be 1');
        }

        return true;
    }

    /**
     * 是否為安全區間
     *
     * @return bool
     */
    public function isSafeRange(): bool
    {
        return collect($this->getActualRangeValue())->filter(function ($range) {
            return $range[0] > $range[1];
        })->isEmpty();
    }
}