<?php
namespace App\Models;

class Cart {
    private $listCartItem = [];
    private $totalQuantity = 0;
    private $grandTotal = 0;

    public function __construct($oldCart = null) {
        if ($oldCart instanceof self) {
            $this->listCartItem = $oldCart->listCartItem ?? [];
            $this->totalQuantity = $oldCart->totalQuantity ?? 0;
            $this->grandTotal = $oldCart->grandTotal ?? 0;
        }
    }

    // Getter & Setter cho danh sách sản phẩm
    public function getListCartItem() {
        return $this->listCartItem;
    }
    public function setListCartItem(array $listCartItem) {
        $this->listCartItem = $listCartItem;
    }

    // Getter & Setter cho tổng số lượng
    public function getTotalQuantity() {
        return $this->totalQuantity;
    }
    public function setTotalQuantity($totalQuantity) {
        $this->totalQuantity = max(0, (int)$totalQuantity); // Đảm bảo không âm
    }

    // Getter & Setter cho tổng tiền
    public function getGrandTotal() {
        return $this->grandTotal;
    }
    public function setGrandTotal($grandTotal) {
        $this->grandTotal = max(0, (float)$grandTotal); // Đảm bảo không âm
    }

    // Thêm sản phẩm vào giỏ hàng
    public function addItem($productId, $price, $quantity = 1) {
        if (isset($this->listCartItem[$productId])) {
            $this->listCartItem[$productId]['quantity'] += $quantity;
        } else {
            $this->listCartItem[$productId] = [
                'price' => $price,
                'quantity' => $quantity
            ];
        }

        $this->updateCart();
    }

    // Xóa sản phẩm khỏi giỏ hàng
    public function removeItem($productId) {
        if (isset($this->listCartItem[$productId])) {
            unset($this->listCartItem[$productId]);
            $this->updateCart();
        }
    }

    // Cập nhật tổng số lượng & tổng tiền
    private function updateCart() {
        $this->totalQuantity = 0;
        $this->grandTotal = 0;

        foreach ($this->listCartItem as $item) {
            $this->totalQuantity += $item['quantity'];
            $this->grandTotal += $item['quantity'] * $item['price'];
        }
    }
}
?>
