<?php

namespace App\Models;

use App\Database\Connection;

class Category
{
    private $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function getAll($activeOnly = true)
    {
        $where = $activeOnly ? "WHERE is_active = 1" : "";
        $sql = "SELECT * FROM categories $where ORDER BY sort_order ASC, name ASC";
        return $this->db->fetchAll($sql);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM categories WHERE id = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    public function getBySlug($slug)
    {
        $sql = "SELECT * FROM categories WHERE slug = :slug AND is_active = 1";
        return $this->db->fetchOne($sql, ['slug' => $slug]);
    }

    public function getTree()
    {
        $categories = $this->getAll();
        return $this->buildTree($categories);
    }

    private function buildTree($categories, $parentId = null)
    {
        $branch = [];
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $children = $this->buildTree($categories, $category['id']);
                if ($children) {
                    $category['children'] = $children;
                }
                $branch[] = $category;
            }
        }
        return $branch;
    }
}

