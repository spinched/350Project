<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Seed session DB on first load
if (!isset($_SESSION['db'])) {
    $_SESSION['db'] = [
        'locations' => [
            ['L_ID'=>400000,'StoreAisle'=>'01','P_Type'=>'vegetables'],
            ['L_ID'=>400001,'StoreAisle'=>'03','P_Type'=>'dairy'],
            ['L_ID'=>400002,'StoreAisle'=>'05','P_Type'=>'poultry'],
            ['L_ID'=>400003,'StoreAisle'=>'06','P_Type'=>'beef'],
            ['L_ID'=>400004,'StoreAisle'=>'08','P_Type'=>'canned goods'],
            ['L_ID'=>400005,'StoreAisle'=>'10','P_Type'=>'frozen goods'],
        ],
        'products' => [
            ['P_ID'=>1000000,'P_Name'=>'Whole Milk','P_Description'=>'Fresh whole milk sourced from local farms. Rich and creamy with a smooth finish.','P_Cost'=>4.99,'P_SaleCost'=>3.49,'P_Weight'=>8.60,'P_CostPerOunce'=>0.04,'QuantityInStock'=>48,'M_ID'=>200000,'L_ID'=>400001],
            ['P_ID'=>1000001,'P_Name'=>'Cheddar Cheese','P_Description'=>'Sharp aged cheddar cheese, great for sandwiches and snacking. 12-month aged.','P_Cost'=>6.49,'P_SaleCost'=>null,'P_Weight'=>1.00,'P_CostPerOunce'=>0.41,'QuantityInStock'=>32,'M_ID'=>200000,'L_ID'=>400001],
            ['P_ID'=>1000002,'P_Name'=>'Roma Tomatoes','P_Description'=>'Vine-ripened Roma tomatoes, perfect for sauces and fresh salads. Locally sourced.','P_Cost'=>2.99,'P_SaleCost'=>1.99,'P_Weight'=>2.00,'P_CostPerOunce'=>0.09,'QuantityInStock'=>120,'M_ID'=>200001,'L_ID'=>400000],
            ['P_ID'=>1000003,'P_Name'=>'Chicken Breast','P_Description'=>'Boneless skinless chicken breasts, fresh and ready to cook. No added hormones.','P_Cost'=>8.99,'P_SaleCost'=>6.99,'P_Weight'=>2.50,'P_CostPerOunce'=>0.22,'QuantityInStock'=>60,'M_ID'=>200001,'L_ID'=>400002],
            ['P_ID'=>1000004,'P_Name'=>'Ground Beef','P_Description'=>'80/20 lean ground beef, great for burgers and meat sauces. Freshly ground daily.','P_Cost'=>10.99,'P_SaleCost'=>null,'P_Weight'=>2.00,'P_CostPerOunce'=>0.34,'QuantityInStock'=>45,'M_ID'=>200000,'L_ID'=>400003],
            ['P_ID'=>1000005,'P_Name'=>'Black Beans','P_Description'=>'Premium canned black beans, no added salt, ready to eat straight from the can.','P_Cost'=>1.29,'P_SaleCost'=>0.99,'P_Weight'=>0.95,'P_CostPerOunce'=>0.09,'QuantityInStock'=>200,'M_ID'=>200001,'L_ID'=>400004],
            ['P_ID'=>1000006,'P_Name'=>'Frozen Broccoli','P_Description'=>'Flash-frozen broccoli florets. Nutritious and ready in minutes with no prep needed.','P_Cost'=>3.49,'P_SaleCost'=>null,'P_Weight'=>1.50,'P_CostPerOunce'=>0.14,'QuantityInStock'=>88,'M_ID'=>200000,'L_ID'=>400005],
            ['P_ID'=>1000007,'P_Name'=>'Baby Spinach','P_Description'=>'Fresh tender baby spinach leaves, triple-washed and ready to serve.','P_Cost'=>3.99,'P_SaleCost'=>2.79,'P_Weight'=>0.31,'P_CostPerOunce'=>0.80,'QuantityInStock'=>8,'M_ID'=>200001,'L_ID'=>400000],
            ['P_ID'=>1000008,'P_Name'=>'Greek Yogurt','P_Description'=>'Thick and creamy plain Greek yogurt, high in protein. Made with live active cultures.','P_Cost'=>5.49,'P_SaleCost'=>4.29,'P_Weight'=>2.00,'P_CostPerOunce'=>0.17,'QuantityInStock'=>56,'M_ID'=>200000,'L_ID'=>400001],
            ['P_ID'=>1000009,'P_Name'=>'Diced Tomatoes','P_Description'=>'Fire-roasted diced tomatoes in tomato juice, great for soups, stews, and pasta.','P_Cost'=>1.79,'P_SaleCost'=>null,'P_Weight'=>0.88,'P_CostPerOunce'=>0.13,'QuantityInStock'=>145,'M_ID'=>200001,'L_ID'=>400004],
        ],
        'it' => [
            ['IT_ID'=>100000,'IT_FirstName'=>'Severus','IT_LastName'=>'Snape','IT_BirthDate'=>'1960-01-09'],
            ['IT_ID'=>100001,'IT_FirstName'=>'Luna','IT_LastName'=>'Lovegood','IT_BirthDate'=>'1981-02-13'],
        ],
        'managers' => [
            ['M_ID'=>200000,'M_FirstName'=>'Minerva','M_LastName'=>'McGonagall','M_BirthDate'=>'1932-10-04','IT_ID'=>100000],
            ['M_ID'=>200001,'M_FirstName'=>'Albus','M_LastName'=>'Dumbledore','M_BirthDate'=>'1881-08-01','IT_ID'=>100001],
        ],
        'stockers' => [
            ['S_ID'=>300000,'S_FirstName'=>'Argus','S_LastName'=>'Filch','S_BirthDate'=>'1944-03-16','M_ID'=>200000,'IT_ID'=>100000],
            ['S_ID'=>300001,'S_FirstName'=>'Neville','S_LastName'=>'Longbottom','S_BirthDate'=>'1980-07-30','M_ID'=>200000,'IT_ID'=>100001],
            ['S_ID'=>300002,'S_FirstName'=>'Lavender','S_LastName'=>'Brown','S_BirthDate'=>'1979-07-12','M_ID'=>200001,'IT_ID'=>100000],
            ['S_ID'=>300003,'S_FirstName'=>'Seamus','S_LastName'=>'Finnigan','S_BirthDate'=>'1980-10-28','M_ID'=>200001,'IT_ID'=>100001],
        ],
        '_nextIT'      => 100002,
        '_nextManager' => 200002,
        '_nextStocker' => 300004,
        '_nextProduct' => 1000010,
    ];
}

// ── Role helpers ──────────────────────────────────────────────
function getRole(): string {
    return $_SESSION['role'] ?? 'Customer';
}

function hasAccess(array $roles): bool {
    return in_array(getRole(), $roles);
}

// ── Data helpers ──────────────────────────────────────────────
function getLocation(int $lid): ?array {
    foreach ($_SESSION['db']['locations'] as $l) {
        if ($l['L_ID'] === $lid) return $l;
    }
    return null;
}

function getProductWithLocation(int $pid): ?array {
    foreach ($_SESSION['db']['products'] as $p) {
        if ($p['P_ID'] === $pid) {
            $loc = getLocation($p['L_ID']);
            return array_merge($p, [
                'StoreAisle' => $loc['StoreAisle'] ?? null,
                'P_Type'     => $loc['P_Type'] ?? null,
            ]);
        }
    }
    return null;
}

function getAllProductsWithLocation(): array {
    return array_map(function($p) {
        $loc = getLocation($p['L_ID']);
        return array_merge($p, [
            'StoreAisle' => $loc['StoreAisle'] ?? null,
            'P_Type'     => $loc['P_Type'] ?? null,
        ]);
    }, $_SESSION['db']['products']);
}

function getAllEmployees(): array {
    $rows = [];
    foreach ($_SESSION['db']['it'] as $e) {
        $rows[] = ['id'=>$e['IT_ID'],'firstName'=>$e['IT_FirstName'],'lastName'=>$e['IT_LastName'],'birthDate'=>$e['IT_BirthDate'],'role'=>'IT','managerID'=>null];
    }
    foreach ($_SESSION['db']['managers'] as $e) {
        $rows[] = ['id'=>$e['M_ID'],'firstName'=>$e['M_FirstName'],'lastName'=>$e['M_LastName'],'birthDate'=>$e['M_BirthDate'],'role'=>'Manager','managerID'=>null];
    }
    foreach ($_SESSION['db']['stockers'] as $e) {
        $rows[] = ['id'=>$e['S_ID'],'firstName'=>$e['S_FirstName'],'lastName'=>$e['S_LastName'],'birthDate'=>$e['S_BirthDate'],'role'=>'Stocker','managerID'=>$e['M_ID']];
    }
    return $rows;
}

// ── Display helpers ───────────────────────────────────────────
function getCategoryEmoji(string $type): string {
    return ['vegetables'=>'🥬','dairy'=>'🥛','poultry'=>'🍗','beef'=>'🥩','canned goods'=>'🥫','frozen goods'=>'❄️'][strtolower($type)] ?? '🛒';
}

function getCategoryClass(string $type): string {
    return ['vegetables'=>'cat-vegetables','dairy'=>'cat-dairy','poultry'=>'cat-poultry','beef'=>'cat-beef','canned goods'=>'cat-canned','frozen goods'=>'cat-frozen'][strtolower($type)] ?? '';
}

function fmtDate(string $d): string {
    if (!$d) return '—';
    [$y, $m, $day] = explode('-', $d);
    return "$m/$day/$y";
}

function fmtPrice(?float $val): string {
    return $val === null ? '—' : '$' . number_format($val, 2);
}

function fmtAisle(string $aisle): string {
    return strlen($aisle) < 2 ? '0' . $aisle : $aisle;
}

// ── Validation ────────────────────────────────────────────────
function validateName(string $val, string $label): ?string {
    if (!$val) return "$label is required.";
    if (!preg_match('/^[A-Za-z]+$/', $val)) return "$label must contain only letters (no spaces).";
    return null;
}

function validatePassword(string $val): ?string {
    if (!$val) return 'Password is required.';
    if (strlen($val) < 12) return 'Password must be at least 12 characters.';
    return null;
}
