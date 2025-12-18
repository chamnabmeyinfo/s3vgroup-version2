<?php

namespace App\Models;

use App\Database\Connection;

class Product
{
    private $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function getAll($filters = [])
    {
        $where = [];
        $params = [];
        
        // Only filter by active if not explicitly set to false (for admin)
        if (!isset($filters['include_inactive']) || !$filters['include_inactive']) {
            $where[] = "p.is_active = 1";
        }
        
        // Handle is_active filter explicitly (for admin filtering)
        if (isset($filters['is_active'])) {
            $where[] = "p.is_active = :is_active";
            $params['is_active'] = (int)$filters['is_active'];
        }

        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['is_featured'])) {
            $where[] = "p.is_featured = :is_featured";
            $params['is_featured'] = (int)$filters['is_featured'];
        } elseif (!empty($filters['featured'])) {
            // Legacy support for 'featured' filter
            $where[] = "p.is_featured = 1";
        }

        if (!empty($filters['search'])) {
            $where[] = "(p.name LIKE :search OR p.description LIKE :search OR p.short_description LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }

        // Price filtering
        if (isset($filters['min_price']) && $filters['min_price'] > 0) {
            $where[] = "COALESCE(p.sale_price, p.price, 0) >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }

        if (isset($filters['max_price']) && $filters['max_price'] > 0) {
            $where[] = "COALESCE(p.sale_price, p.price, 0) <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }

        // Stock status filtering
        if (!empty($filters['in_stock'])) {
            $where[] = "p.stock_status = 'in_stock'";
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        // Use limit from filters, default to 20 for admin, 12 for frontend
        $limit = isset($filters['limit']) ? (int)$filters['limit'] : (isset($filters['include_inactive']) ? 20 : 12);
        $page = (int)($filters['page'] ?? 1);
        $offset = ($page - 1) * $limit;

        // Sorting
        $orderBy = "p.is_featured DESC, p.created_at DESC";
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'name':
                    $orderBy = "p.name ASC";
                    break;
                case 'name_desc':
                    $orderBy = "p.name DESC";
                    break;
                case 'price_asc':
                    $orderBy = "COALESCE(p.sale_price, p.price, 0) ASC";
                    break;
                case 'price_desc':
                    $orderBy = "COALESCE(p.sale_price, p.price, 0) DESC";
                    break;
                case 'newest':
                    $orderBy = "p.created_at DESC";
                    break;
                case 'featured':
                    $orderBy = "p.is_featured DESC, p.created_at DESC";
                    break;
            }
        }

        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                $whereClause
                ORDER BY $orderBy
                LIMIT $limit OFFSET $offset";

        return $this->db->fetchAll($sql, $params);
    }

    public function getById($id)
    {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id = :id";
        
        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    public function getBySlug($slug)
    {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.slug = :slug AND p.is_active = 1";
        
        $product = $this->db->fetchOne($sql, ['slug' => $slug]);
        
        if ($product) {
            $this->incrementViewCount($product['id']);
        }
        
        return $product;
    }

    public function getFeatured($limit = 6)
    {
        return $this->getAll(['featured' => true, 'limit' => $limit]);
    }

    public function incrementViewCount($id)
    {
        $this->db->query("UPDATE products SET view_count = view_count + 1 WHERE id = :id", ['id' => $id]);
    }

    public function count($filters = [])
    {
        $where = ["is_active = 1"];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = "category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(name LIKE :search OR description LIKE :search OR short_description LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['featured'])) {
            $where[] = "is_featured = 1";
        }

        // Price filtering
        if (isset($filters['min_price']) && $filters['min_price'] > 0) {
            $where[] = "COALESCE(sale_price, price, 0) >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }

        if (isset($filters['max_price']) && $filters['max_price'] > 0) {
            $where[] = "COALESCE(sale_price, price, 0) <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }

        // Stock status filtering
        if (!empty($filters['in_stock'])) {
            $where[] = "stock_status = 'in_stock'";
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) as total FROM products WHERE $whereClause";
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }

    public function create($data)
    {
        return $this->db->insert('products', $data);
    }

    public function update($id, $data)
    {
        return $this->db->update('products', $data, 'id = :id', ['id' => $id]);
    }

    public function delete($id)
    {
        // Validate ID
        if (empty($id) || !is_numeric($id)) {
            throw new \Exception('Invalid product ID');
        }
        
        $id = (int)$id;
        
        // Check if product exists
        $product = $this->getById($id);
        if (!$product) {
            throw new \Exception('Product not found');
        }
        
        // Check for order items - if product is in orders, we should prevent deletion or handle carefully
        try {
            $orderItems = $this->db->fetchAll(
                "SELECT COUNT(*) as count FROM order_items WHERE product_id = :id",
                ['id' => $id]
            );
            $orderItemCount = $orderItems[0]['count'] ?? 0;
            
            if ($orderItemCount > 0) {
                // Product has been ordered - we'll still delete but log it
                // In production, you might want to prevent this or set a flag
            }
        } catch (\Exception $e) {
            // Order items table might not exist, continue
        }
        
        // Delete related variants
        try {
            // Get variant IDs first
            $variants = $this->db->fetchAll(
                "SELECT id FROM product_variants WHERE product_id = :id",
                ['id' => $id]
            );
            
            if (!empty($variants)) {
                $variantIds = array_column($variants, 'id');
                $placeholders = implode(',', array_fill(0, count($variantIds), '?'));
                
                // Delete variant attributes
                $this->db->query(
                    "DELETE FROM product_variant_attributes WHERE variant_id IN ($placeholders)",
                    $variantIds
                );
            }
            
            // Delete variants
            $this->db->delete('product_variants', 'product_id = :id', ['id' => $id]);
        } catch (\Exception $e) {
            // Variants table might not exist, continue
        }
        
        // Perform hard delete
        $deleted = $this->db->delete('products', 'id = :id', ['id' => $id]);
        
        if ($deleted === 0) {
            throw new \Exception('Failed to delete product');
        }
        
        return true;
    }
}