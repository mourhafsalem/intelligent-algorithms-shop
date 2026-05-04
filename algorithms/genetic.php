<?php
declare(strict_types=1);

/**
 * GeneticRecommender
 * -------------------
 * خوارزمية جينية لاقتراح K منتجات مخصصة لمستخدم.
 *
 * الكروموسوم: مصفوفة من K معرفات منتجات (بدون تكرار)
 * الجين: product_id
 *
 * Fitness = w1*التشابه_التعاوني + w2*السلوك + w3*الشعبية + w4*التنوع
 *
 * المراحل:
 *   1. تكوين السكان الابتدائي (عشوائي مع تحيز للمنتجات الشائعة)
 *   2. تقييم اللياقة لكل كروموسوم
 *   3. الاختيار: Tournament Selection (k=3)
 *   4. التزاوج: Order-1 Crossover (يضمن عدم التكرار)
 *   5. الطفرة: استبدال جين عشوائي بمنتج آخر
 *   6. النخبوية (Elitism): الاحتفاظ بأفضل كروموسوم
 *   7. التكرار حتى انتهاء الأجيال
 */
class GeneticRecommender
{
    private int $userId;
    private int $K;
    private int $popSize;
    private int $generations;
    private float $mutationRate;

    private array $allProductIds;
    private array $productsById;
    private array $userRatings;        // [pid => rating]
    private array $userBehavior;       // [pid => score]
    private array $popularity;         // [pid => count]
    private array $similarUsersRatings;// [pid => avg_rating من مستخدمين مشابهين]

    public array $convergence = [];    // best fitness لكل جيل (للرسم في الأدمن)

    public function __construct(
        int $userId,
        int $K = 8,
        int $popSize = 60,
        int $generations = 40,
        float $mutationRate = 0.1
    ) {
        $this->userId = $userId;
        $this->K = $K;
        $this->popSize = $popSize;
        $this->generations = $generations;
        $this->mutationRate = $mutationRate;
        $this->prepare();
    }

    private function prepare(): void
    {
        $this->productsById = DataStore::productsById();
        $this->allProductIds = array_keys($this->productsById);

        // تقييمات المستخدم
        $this->userRatings = [];
        foreach (DataStore::ratingsByUser($this->userId) as $r) {
            $this->userRatings[(int)$r['product_id']] = (float)$r['rating'];
        }

        // سلوك المستخدم: views=1, clicks=2, purchases=3
        $this->userBehavior = [];
        foreach (DataStore::behaviorByUser($this->userId) as $b) {
            $pid = (int)$b['product_id'];
            $score = ((int)$b['viewed']) * 1
                   + ((int)$b['clicked']) * 2
                   + ((int)$b['purchased']) * 3;
            $this->userBehavior[$pid] = $score;
        }

        // شعبية المنتجات (عدد التقييمات الإجمالي)
        $this->popularity = [];
        foreach (DataStore::ratings() as $r) {
            $pid = (int)$r['product_id'];
            $this->popularity[$pid] = ($this->popularity[$pid] ?? 0) + 1;
        }

        // التصفية التعاونية المبسطة: ابحث عن مستخدمين شبيهين
        // (لديهم نفس الفئة العمرية والبلد) واحسب متوسط تقييماتهم.
        $this->similarUsersRatings = $this->buildSimilarUsersRatings();
    }

    private function buildSimilarUsersRatings(): array
    {
        $me = DataStore::userById($this->userId);
        if (!$me) return [];

        $similarIds = [];
        foreach (DataStore::users() as $u) {
            if ((int)$u['user_id'] === $this->userId) continue;
            $ageDiff = abs((int)$u['age'] - (int)$me['age']);
            if ($ageDiff <= 5 && $u['country'] === $me['country']) {
                $similarIds[(int)$u['user_id']] = true;
            }
        }
        if (!$similarIds) return [];

        $sum = []; $cnt = [];
        foreach (DataStore::ratings() as $r) {
            $uid = (int)$r['user_id'];
            if (!isset($similarIds[$uid])) continue;
            $pid = (int)$r['product_id'];
            $sum[$pid] = ($sum[$pid] ?? 0) + (float)$r['rating'];
            $cnt[$pid] = ($cnt[$pid] ?? 0) + 1;
        }
        $avg = [];
        foreach ($sum as $pid => $s) $avg[$pid] = $s / $cnt[$pid];
        return $avg;
    }

    // ============ دالة اللياقة ============
    public function fitness(array $chromosome): float
    {
        $w1 = 1.5; // collaborative
        $w2 = 1.2; // behavior
        $w3 = 0.6; // popularity
        $w4 = 0.8; // diversity

        $cfScore = 0.0; $bhScore = 0.0; $popScore = 0.0;
        $cats = [];

        foreach ($chromosome as $pid) {
            $cfScore  += $this->similarUsersRatings[$pid] ?? 0.0;
            $bhScore  += $this->userBehavior[$pid] ?? 0.0;
            $popScore += log(1 + ($this->popularity[$pid] ?? 0));
            if (isset($this->productsById[$pid])) {
                $cats[$this->productsById[$pid]['category']] = true;
            }
            // عقوبة على المنتجات اللي اشتراها فعلاً
            if (!empty($this->userBehavior[$pid]) && $this->userBehavior[$pid] >= 3) {
                $bhScore -= 1.0;
            }
        }

        $diversity = count($cats); // عدد الفئات الفريدة
        $K = max(1, count($chromosome));

        return $w1 * ($cfScore / $K)
             + $w2 * ($bhScore / $K)
             + $w3 * ($popScore / $K)
             + $w4 * $diversity;
    }

    // ============ توليد كروموسوم عشوائي ============
    private function randomChromosome(): array
    {
        $pids = $this->allProductIds;
        shuffle($pids);
        return array_slice($pids, 0, $this->K);
    }

    // ============ Tournament Selection ============
    private function tournament(array $pop, array $fits, int $k = 3): array
    {
        $best = null; $bestFit = -INF;
        $n = count($pop);
        for ($i = 0; $i < $k; $i++) {
            $idx = random_int(0, $n - 1);
            if ($fits[$idx] > $bestFit) { $bestFit = $fits[$idx]; $best = $pop[$idx]; }
        }
        return $best;
    }

    // ============ Order-1 Crossover (بدون تكرار) ============
    private function crossover(array $p1, array $p2): array
    {
        $K = $this->K;
        $a = random_int(0, $K - 1);
        $b = random_int(0, $K - 1);
        if ($a > $b) [$a, $b] = [$b, $a];

        $child = array_fill(0, $K, null);
        // انسخ شريحة من الأب الأول
        $taken = [];
        for ($i = $a; $i <= $b; $i++) {
            $child[$i] = $p1[$i];
            $taken[$p1[$i]] = true;
        }
        // املأ الباقي من الأب الثاني (بدون تكرار)
        $j = 0;
        for ($i = 0; $i < $K; $i++) {
            if ($child[$i] !== null) continue;
            while ($j < $K && isset($taken[$p2[$j]])) $j++;
            if ($j < $K) {
                $child[$i] = $p2[$j];
                $taken[$p2[$j]] = true;
                $j++;
            } else {
                // ملء احتياطي عشوائي
                foreach ($this->allProductIds as $pid) {
                    if (!isset($taken[$pid])) {
                        $child[$i] = $pid; $taken[$pid] = true; break;
                    }
                }
            }
        }
        return $child;
    }

    // ============ الطفرة ============
    private function mutate(array $chromo): array
    {
        if (mt_rand() / mt_getrandmax() > $this->mutationRate) return $chromo;
        $idx = random_int(0, $this->K - 1);
        $taken = array_flip($chromo);
        do {
            $newPid = $this->allProductIds[array_rand($this->allProductIds)];
        } while (isset($taken[$newPid]));
        $chromo[$idx] = $newPid;
        return $chromo;
    }

    // ============ التشغيل الرئيسي ============
    public function run(): array
    {
        $this->convergence = [];

        // السكان الابتدائي
        $pop = [];
        for ($i = 0; $i < $this->popSize; $i++) {
            $pop[] = $this->randomChromosome();
        }

        $bestEver = null; $bestEverFit = -INF;

        for ($g = 0; $g < $this->generations; $g++) {
            // تقييم
            $fits = [];
            foreach ($pop as $c) $fits[] = $this->fitness($c);

            // أفضل في هذا الجيل
            $maxIdx = 0;
            for ($i = 1; $i < count($fits); $i++) {
                if ($fits[$i] > $fits[$maxIdx]) $maxIdx = $i;
            }
            if ($fits[$maxIdx] > $bestEverFit) {
                $bestEverFit = $fits[$maxIdx];
                $bestEver = $pop[$maxIdx];
            }
            $this->convergence[] = $fits[$maxIdx];

            // الجيل الجديد - النخبوية: نحتفظ بأفضل واحد
            $newPop = [$pop[$maxIdx]];
            while (count($newPop) < $this->popSize) {
                $p1 = $this->tournament($pop, $fits);
                $p2 = $this->tournament($pop, $fits);
                $child = $this->crossover($p1, $p2);
                $child = $this->mutate($child);
                $newPop[] = $child;
            }
            $pop = $newPop;
        }

        // ترتيب الكروموسوم النهائي حسب جاذبية كل جين
        usort($bestEver, function($a, $b) {
            $sa = ($this->similarUsersRatings[$a] ?? 0) + ($this->userBehavior[$a] ?? 0) * 0.5;
            $sb = ($this->similarUsersRatings[$b] ?? 0) + ($this->userBehavior[$b] ?? 0) * 0.5;
            return $sb <=> $sa;
        });

        return [
            'recommendations' => $bestEver,
            'fitness'         => $bestEverFit,
            'convergence'     => $this->convergence,
        ];
    }
}
