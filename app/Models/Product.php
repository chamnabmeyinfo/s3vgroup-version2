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
        return $this->db->update('products', ['is_active' => 0], 'id = :id', ['id' => $id]);
    }
}

