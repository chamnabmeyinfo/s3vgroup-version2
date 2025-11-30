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

        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['featured'])) {
            $where[] = "p.is_featured = 1";
        }

        if (!empty($filters['search'])) {
            $where[] = "(p.name LIKE :search OR p.description LIKE :search OR p.short_description LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $limit = (int)($filters['limit'] ?? 12);
        $page = (int)($filters['page'] ?? 1);
        $offset = ($page - 1) * $limit;

        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                $whereClause
                ORDER BY p.is_featured DESC, p.created_at DESC
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
            $where[] = "(name LIKE :search OR description LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
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

