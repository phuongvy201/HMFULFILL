# Logic Tính Giá Khi Quantity > 1

## Tổng quan

Khi một sản phẩm có `quantity > 1`, logic tính giá sẽ tách biệt:

-   **1 item đầu tiên**: Tính theo giá "1st item"
-   **Các item còn lại**: Tính theo giá "2nd item" (thường rẻ hơn)

## Ví dụ cụ thể

### Trường hợp 1: Product có quantity = 2

```json
{
    "part_number": "S001-WHIT-M-1S-UK",
    "quantity": 2,
    "shipping_method": "seller_label"
}
```

**Giả sử giá shipping:**

-   `seller_1st`: $15.00
-   `seller_next`: $12.00

**Tính toán:**

-   Item 1: $15.00 (1st item price)
-   Item 2: $12.00 (next item price)
-   **Total**: $27.00
-   **Average price** (lưu DB): $13.50

### Trường hợp 2: Product có quantity = 3

```json
{
    "part_number": "S001-WHIT-M-1S-UK",
    "quantity": 3,
    "shipping_method": "seller_label"
}
```

**Tính toán:**

-   Item 1: $15.00 (1st item price)
-   Item 2: $12.00 (next item price)
-   Item 3: $12.00 (next item price)
-   **Total**: $39.00
-   **Average price** (lưu DB): $13.00

## Response API mới

Với logic cập nhật, response sẽ có thêm `pricing_detail`:

```json
{
    "part_number": "S001-WHIT-M-1S-UK",
    "title": "T-Shirt Campaign",
    "quantity": 2,
    "print_price": "13.50",
    "total_price": "27.00",
    "pricing_detail": {
        "type": "mixed",
        "first_item_price": "15.00",
        "additional_item_price": "12.00",
        "breakdown": "1x15.00 + 1x12.00"
    }
}
```

## Lưu ý quan trọng

1. **Chỉ item có giá 1st cao nhất** mới được áp dụng mixed pricing
2. **Các item khác** đều tính theo giá 2nd item
3. **Average price** được lưu vào database để dễ quản lý
4. **Pricing detail** trong response giúp khách hàng hiểu rõ cách tính giá
