<?php
/** Shared server-side rules for every sellable SKU. */
class CatalogRules {
    public static function retryable(Throwable $error) {
        return $error instanceof PDOException && (in_array((int)($error->errorInfo[1] ?? 0), [1020,1205,1213], true) || $error->getCode()==='40001');
    }
    public static function quantity($value, $allowZero = false) {
        $result = filter_var($value, FILTER_VALIDATE_INT);
        if ($result === false || $result < ($allowZero ? 0 : 1) || $result > 2147483647) {
            throw new InvalidArgumentException('Quantity must be a valid ' . ($allowZero ? 'non-negative' : 'positive') . ' integer.');
        }
        return $result;
    }

    public static function money($value) {
        if (!is_numeric($value) || !is_finite((float)$value) || $value < 0 || $value > 99999999.99) {
            throw new InvalidArgumentException('Price must be a valid non-negative amount.');
        }
        return number_format(round((float)$value, 2), 2, '.', '');
    }

    public static function price($price, $discount) {
        if (!is_numeric($discount) || $discount < 0 || $discount > 100) {
            throw new InvalidArgumentException('Discount must be between 0 and 100.');
        }
        $cents = (int)round((float)self::money($price) * 100);
        $basisPoints = (int)round((float)$discount * 100);
        return number_format(intdiv($cents * (10000 - $basisPoints) + 5000, 10000) / 100, 2, '.', '');
    }

    public static function hasVariants(PDO $pdo, $productId) {
        $stmt = $pdo->prepare('SELECT EXISTS(SELECT 1 FROM product_variants WHERE product_id = ?)');
        $stmt->execute([$productId]);
        return (bool)$stmt->fetchColumn();
    }

    public static function syncStock(PDO $pdo, $productId) {
        if (self::hasVariants($pdo, $productId)) {
            $pdo->prepare('UPDATE products SET stock_quantity = (SELECT COALESCE(SUM(stock_quantity),0) FROM product_variants WHERE product_id = ? AND is_active = 1) WHERE id = ?')->execute([$productId, $productId]);
        }
    }

    public static function sellable(PDO $pdo, $productId, $variantId = null, $lock = false) {
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?' . ($lock ? ' FOR UPDATE' : ''));
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        if (!$product || !$product['is_active']) throw new DomainException('This product is no longer available.');
        $variant = null;
        if ($variantId) {
            $stmt = $pdo->prepare('SELECT * FROM product_variants WHERE id = ? AND product_id = ?' . ($lock ? ' FOR UPDATE' : ''));
            $stmt->execute([$variantId, $productId]);
            $variant = $stmt->fetch();
            if (!$variant || !$variant['is_active']) throw new DomainException('This variant is no longer available. Please choose another option.');
        } elseif (self::hasVariants($pdo, $productId)) {
            throw new DomainException('Please choose product options before adding this item.');
        }
        $attributes = null;
        if ($variant) {
            $stmt = $pdo->prepare('SELECT attribute_name, attribute_value FROM variant_attributes WHERE product_variant_id = ? ORDER BY attribute_name');
            $stmt->execute([$variantId]);
            $attributes = json_encode($stmt->fetchAll(PDO::FETCH_KEY_PAIR), JSON_UNESCAPED_UNICODE);
        }
        return [
            'product_id' => (int)$productId, 'variant_id' => $variant ? (int)$variantId : null,
            'product_name' => $product['name_en'], 'product_sku' => $variant['sku'] ?? $product['sku'],
            'variant_name' => $variant['variant_name'] ?? null, 'attributes_json' => $attributes,
            'stock_quantity' => (int)($variant['stock_quantity'] ?? $product['stock_quantity']),
            'price_at_time' => self::price($variant['price'] ?? $product['price'], $product['discount_percentage'])
        ];
    }

    public static function variantsFromForm(array $rows, $basePrice) {
        $result = [];
        foreach ($rows as $row) {
            $attributes = [];
            foreach ($row['attributes']['name'] ?? [] as $index => $name) {
                $name = trim($name);
                $value = trim($row['attributes']['value'][$index] ?? '');
                if ($name === '' && $value === '') continue;
                if ($name === '' || $value === '') throw new InvalidArgumentException('Each option needs a name and value.');
                $key = mb_strtolower($name);
                if (isset($attributes[$key])) throw new InvalidArgumentException('Duplicate option name: ' . $name);
                $attributes[$key] = $value;
            }
            $result[] = ['id' => $row['id'] ?? null, 'sku' => $row['sku'] ?? '',
                'variant_name' => trim($row['name'] ?? ''),
                'price' => ($row['price'] ?? '') === '' ? $basePrice : $row['price'],
                'stock_quantity' => $row['stock'] ?? 0, 'attributes' => $attributes];
        }
        return $result;
    }
}
