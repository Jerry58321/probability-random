# **ProbabilityRandom**

ProbabilityRandom 是一個用於根據區間及機率隨機生成數字的 PHP Composer package。

## **安裝**

---

可以使用 Composer 來安裝 ProbabilityRandom。請在你的專案目錄中運行以下命令：

```
composer require jerry58321/probability-random
```

## **用法**

---

### **初始化**

首先，你需要將 **`ProbabilityRandom`** 類別引入你的程式碼中：

```php
use Jerry58321\ProbabilityRandom\ProbabilityRandom;
```

然後，你可以通過以下方式來初始化一個 **`ProbabilityRandom`** 實例：

```php
$min = 1; // 最小值
$max = 100; // 最大值
$probabilityRandom = ProbabilityRandom::build($min, $max);
// 注意：build method 第三個參數為是否使用安全區間，
// 若設定不啟用(false)時，遇到 min > max 情況時則會拋出例外，預設為(true)。
// 若採用預設啟用(true)，遇到 min > max 情況時則不採用區間機率(等於使用 random_int)
```

### **設定區間比例及區間機率**

你可以使用：

**`setRangeProportions`** 方法設定區間比例，區間比例是一個數值陣列，每個數值表示該區間在整個範圍內的比例。

**`setRangeProbabilities`** 方法設定區間機率，區間機率是一個數值陣列，每個數值表示該區間的機率，所有數值的總和必須為 1。

＊注意：區間機率的陣列數量必須為區間比例的陣列數量+1
```php
/**
 * e.g. 假設 min=1, max=1000
 * 區間1比例=0.04, 範圍=1(min) ~ 40       ,機率=0.01 (1%)
 * 區間2比例=0.08, 範圍=41 ~ 119          ,機率=0.36 (36%)
 * 區間3比例=0.2,  範圍=120 ~ 318         ,機率=0.34 (34%)
 * 區間4比例=0.08, 範圍=319 ~ 397         ,機率=0.2  (20%)
 * 區間5比例=0.17, 範圍=398 ~ 566         ,機率=0.06 (6%)
 * 區間6          範圍=567 ~ 1000(max)   ,機率=0.03 (3%)
 */
$min = 1; // 最小隨機值
$max = 1000; // 最大隨機值
$proportions = [0.04, 0.08, 0.2, 0.08, 0.17]; // 區間比例
$probabilities = [0.01, 0.36, 0.34, 0.2, 0.06, 0.03]; // 區間機率

$probabilityRandom = ProbabilityRandom::build($min, $max)
    ->setRangeProportions($proportions)
    ->setProbabilities($probabilities);

```

### **生成隨機數字**

使用 **`random`** 方法可以生成一個隨機數字，該數字會根據設定的區間比例和機率進行隨機生成。

```php
$randomNumber = $probabilityRandom->random();
```

### **其他方法**

**`ProbabilityRandom`** 類別還提供了一些其他方法：

- **`getActualRangeValue`**：取得實際區間數值
- **`getExpectValue`**：取得期望值
- **`checkRangeSettingLegal`**：檢查區間設定是否合法
- **`checkProbabilitiesSettingLegal`**：檢查機率設定是否合法
- **`isSafeRange`**：是否為安全區間

詳細的方法使用方式可以參考程式碼中的註釋。

## **單元測試**

---

本包已提供了一些單元測試範例，你可以在 **`ProbabilityRandomTest.php`** 檔案中找到這些測試案例。