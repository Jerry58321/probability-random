<?php

use Jerry58321\ProbabilityRandom\ProbabilityRandom;
use PHPUnit\Framework\TestCase;

class ProbabilityRandomTest extends TestCase
{
    /**
     * 測試 random 結果在 min ~ max 範圍內
     *
     * @dataProvider getTestRandomResultInRangeData
     * @return void
     * @throws \Exception
     */
    public function testRandomResultInRange(int $min, int $max, array $probabilities, array $proportions)
    {
        $probabilityRandom = ProbabilityRandom::build($min, $max)
            ->setRangeProbabilities($probabilities)
            ->setRangeProportions($proportions);

        $values = [];
        foreach (range(0, 1000) as $i) {
            $values[] = $probabilityRandom->random();
        }

        $result = collect($values)->filter(function ($value) use ($min, $max) {
            return $value < $min || $value > $max;
        })->toArray();

        $this->assertEmpty($result);
    }

    /**
     * 測試期望值是否正確
     *
     * @dataProvider getMinAndMaxData
     * @return void
     * @throws \Exception
     */
    public function testRandomExpectValueIsCorrect(int $min, int $max, int $expectValue, int $tolerance)
    {
        $probabilityRandom = ProbabilityRandom::build($min, $max)
            ->setRangeProbabilities([0.01, 0.36, 0.34, 0.2, 0.06, 0.03])
            ->setRangeProportions([0.04, 0.08, 0.2, 0.08, 0.17]);

        $result = [];
        foreach (range(0, 100000) as $i) {
            $result[] = $probabilityRandom->random();
        }

        $this->assertTrue(abs(collect($result)->avg() - $expectValue) <= $tolerance);
        $this->assertTrue(abs($probabilityRandom->getExpectValue() - $expectValue) <= $tolerance);
    }

    /**
     * 測試範圍設定是否合法，預期失敗
     *
     * @dataProvider getInvalidSettingData
     * @return void
     */
    public function testCheckRangeSettingLegalExpectFail(array $probabilities, array $proportions)
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The setting of "probabilities" must be "proportions" quantity +1');

        ProbabilityRandom::build(1, 1000)
            ->setRangeProbabilities($probabilities)
            ->setRangeProportions($proportions)
            ->checkRangeSettingLegal();
    }

    /**
     * 測試範圍設定是否合法，預期成功
     *
     * @dataProvider getValidSettingData
     * @return void
     */
    public function testCheckRangeSettingLegalExpectSuccess(array $probabilities, array $proportions)
    {
        $result = ProbabilityRandom::build(1, 1000)
            ->setRangeProbabilities($probabilities)
            ->setRangeProportions($proportions)
            ->checkRangeSettingLegal();

        $this->assertTrue($result);
    }

    /**
     * 測試檢查機率設定是否合法，預期失敗
     *
     * @dataProvider getInvalidProbabilitiesData
     * @return void
     */
    public function testCheckProbabilitiesSettingLegalExpectFail(array $probabilities)
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('sum of "probabilities" must be 1');

        ProbabilityRandom::build(1, 1000)
            ->setRangeProbabilities($probabilities)
            ->checkProbabilitiesSettingLegal();
    }

    /**
     * 測試檢查機率設定是否合法，預期成功
     *
     * @dataProvider getValidProbabilitiesData
     * @return void
     */
    public function testCheckProbabilitiesSettingLegalExpectSuccess(array $probabilities)
    {
        $result = ProbabilityRandom::build(1, 1000)
            ->setRangeProbabilities($probabilities)
            ->checkProbabilitiesSettingLegal();

        $this->assertTrue($result);
    }

    /**
     * 測試取得實際區間金額是否正確
     *
     * @dataProvider getProportionsWithExpectRangeAmount
     * @return void
     */
    public function testGetActualRangeAmountIsCorrect(array $proportions, array $expectRangeAmount)
    {
        $result = ProbabilityRandom::build(1, 1000)
            ->setRangeProportions($proportions)
            ->getActualRangeAmount();

        $this->assertEquals($result, $expectRangeAmount);
    }

    /**
     * 測試是否為安全範圍
     *
     * @dataProvider getTestSafeRangeProportionsData
     * @return void
     */
    public function testIsSafeRange($min, $max, array $proportions, $expectResult)
    {
        $result = ProbabilityRandom::build($min, $max)
            ->setRangeProportions($proportions)
            ->isSafeRange();

        $this->assertEquals($result, $expectResult);
    }

    /**
     * @return array[]
     */
    public function getMinAndMaxData(): array
    {
        return [
            [
                1, 1000, 227, 2
            ],
            [
                1, 10000, 2283, 20
            ],
            [
                1, 5, 3, 1
            ]
        ];
    }

    /**
     * @return array[]
     */
    public function getInvalidSettingData(): array
    {
        return [
            [
                [0.01, 0.36, 0.34, 0.2, 0.06],
                [0.04, 0.08, 0.2, 0.08, 0.17]
            ],
            [
                [0.01, 0.36, 0.34, 0.2, 0.06],
                [0.04, 0.08, 0.2, 0.08, 0.17, 0.1]
            ],
            [
                [0.01, 0.36, 0.34, 0.2, 0.06],
                [0.04]
            ],
            [
                [0.01],
                [0.04, 0.08, 0.2, 0.08, 0.17, 0.1]
            ],
            [
                [],
                [0.3]
            ]
        ];
    }

    /**
     * @return array
     */
    public function getValidSettingData(): array
    {
        return [
            [
                [],
                [],
            ],
            [
                [0.01],
                [],
            ],
            [
                [0.01, 0.36, 0.34, 0.2, 0.06, 0.03],
                [0.04, 0.08, 0.2, 0.08, 0.17]
            ]
        ];
    }

    /**
     * @return \array[][]
     */
    public function getInvalidProbabilitiesData(): array
    {
        return [
            [
                [0.1, 0.2, 0.3]
            ],
            [
                [0.5, 0.6, 0.7]
            ]
        ];
    }

    /**
     * @return \array[][]
     */
    public function getValidProbabilitiesData(): array
    {
        return [
            [
                [0.1, 0.2, 0.3, 0.4]
            ],
            [
                [0.5, 0.5]
            ],
            [
                [0.1, 0.7, 0.2]
            ],
            [
                [1]
            ],
            [
                []
            ]
        ];
    }

    /**
     * @return array[]
     */
    public function getProportionsWithExpectRangeAmount(): array
    {
        return [
            [
                [0.04, 0.08, 0.2, 0.08, 0.17],
                [
                    [1, 40],
                    [41, 119],
                    [120, 318],
                    [319, 397],
                    [398, 566],
                    [567, 1000]
                ]
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function getTestSafeRangeProportionsData(): array
    {
        return [
            [
                1,
                1000,
                [0.04, 0.08, 0.2, 0.08, 0.17],
                true
            ],
            [
                1,
                10,
                [0.04, 0.08, 0.2, 0.08, 0.17],
                false
            ],
            [
                1,
                2000,
                [1.5, 0.1, 0.2, 0.3],
                false
            ]
        ];
    }

    /**
     * @return array[]
     */
    public function getTestRandomResultInRangeData(): array
    {
        return [
            [
                1,
                1000,
                [0.01, 0.36, 0.34, 0.2, 0.06, 0.03],
                [0.04, 0.08, 0.2, 0.08, 1.5]
            ],
            [
                100,
                2000,
                [0.01, 0.36, 0.34, 0.2, 0.06, 0.03],
                [0.04, 0.08, 0.2, 0.08, 0.1]
            ],
            [
                50,
                70,
                [],
                []
            ],
            [
                30,
                60,
                [0.2, 0.7, 0.1],
                [100, 0.0001]
            ],
            [
                50,
                2000,
                [0.1, 0.9],
                [2000]
            ]
        ];
    }
}
