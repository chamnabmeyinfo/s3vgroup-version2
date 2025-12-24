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

    public function getChildren($parentId, $activeOnly = true)
    {
        $where = "WHERE parent_id = :parent_id";
        if ($activeOnly) {
            $where .= " AND is_active = 1";
        }
        $sql = "SELECT * FROM categories $where ORDER BY sort_order ASC, name ASC";
        return $this->db->fetchAll($sql, ['parent_id' => $parentId]);
    }

    public function getParent($categoryId)
    {
        $category = $this->getById($categoryId);
        if ($category && !empty($category['parent_id'])) {
            return $this->getById($category['parent_id']);
        }
        return null;
    }

    public function getAncestors($categoryId)
    {
        $ancestors = [];
        $category = $this->getById($categoryId);
        
        while ($category && !empty($category['parent_id'])) {
            $parent = $this->getById($category['parent_id']);
            if ($parent) {
                array_unshift($ancestors, $parent);
                $category = $parent;
            } else {
                break;
            }
        }
        
        return $ancestors;
    }

    public function getDescendants($categoryId, $includeSelf = false)
    {
        $descendants = [];
        $children = $this->getChildren($categoryId, false);
        
        foreach ($children as $child) {
            $descendants[] = $child['id'];
            $childDescendants = $this->getDescendants($child['id'], false);
            $descendants = array_merge($descendants, $childDescendants);
        }
        
        return array_unique($descendants);
    }

    public function getTree($parentId = null, $activeOnly = true)
    {
        $categories = $this->getAll($activeOnly);
        return $this->buildTree($categories, $parentId);
    }

    private function buildTree($categories, $parentId = null)
    {
        $branch = [];
        foreach ($categories as $category) {
            $catParentId = $category['parent_id'] ?? null;
            if (($catParentId === null && $parentId === null) || 
                ($catParentId !== null && (int)$catParentId === (int)$parentId)) {
                $children = $this->buildTree($categories, $category['id']);
                if (!empty($children)) {
                    $category['children'] = $children;
                }
                $branch[] = $category;
            }
        }
        return $branch;
    }

    public function getFlatTree($parentId = null, $activeOnly = true, $level = 0, $excludeId = null)
    {
        $result = [];
        $categories = $this->getAll($activeOnly);
        
        foreach ($categories as $category) {
            if ($excludeId && $category['id'] == $excludeId) {
                continue;
            }
            
            $catParentId = $category['parent_id'] ?? null;
            if (($catParentId === null && $parentId === null) || 
                ($catParentId !== null && (int)$catParentId === (int)$parentId)) {
                $category['level'] = $level;
                $result[] = $category;
                
                $children = $this->getFlatTree($category['id'], $activeOnly, $level + 1, $excludeId);
                $result = array_merge($result, $children);
            }
        }
        
        return $result;
    }

    public function create($data)
    {
        try {
            $fields = [
                'name', 'slug', 'description', 'image', 'parent_id', 
                'sort_order', 'is_active'
            ];
            
            $insertData = [];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $insertData[$field] = $data[$field];
                }
            }
            
            // Validate required fields
            if (empty($insertData['name'])) {
                return false;
            }
            
            // Generate slug if not provided
            if (empty($insertData['slug']) && !empty($insertData['name'])) {
                $insertData['slug'] = strtolower(trim(preg_replace('/[^a-z0-9-]+/', '-', $insertData['name']), '-'));
            }
            
            // Handle parent_id
            if (isset($insertData['parent_id'])) {
                if ($insertData['parent_id'] === '' || $insertData['parent_id'] === 0) {
                    $insertData['parent_id'] = null;
                } else {
                    $insertData['parent_id'] = (int)$insertData['parent_id'];
                    // Validate parent exists
                    $parent = $this->getById($insertData['parent_id']);
                    if (!$parent) {
                        $insertData['parent_id'] = null;
                    }
                }
            }
            
            // Set defaults
            if (!isset($insertData['is_active'])) {
                $insertData['is_active'] = 1;
            }
            if (!isset($insertData['sort_order'])) {
                $insertData['sort_order'] = 0;
            }
            
            return $this->db->insert('categories', $insertData);
        } catch (\Exception $e) {
            error_log('Category create error: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data)
    {
        try {
            $fields = [
                'name', 'slug', 'description', 'image', 'parent_id', 
                'sort_order', 'is_active'
            ];
            
            $updateData = [];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }
            
            // Handle parent_id
            if (isset($updateData['parent_id'])) {
                if ($updateData['parent_id'] === '' || $updateData['parent_id'] === 0) {
                    $updateData['parent_id'] = null;
                } else {
                    $updateData['parent_id'] = (int)$updateData['parent_id'];
                    
                    // Prevent self-reference
                    if ($updateData['parent_id'] == $id) {
                        unset($updateData['parent_id']);
                    } else {
                        // Prevent circular reference
                        $descendants = $this->getDescendants($id, false);
                        if (in_array($updateData['parent_id'], $descendants)) {
                            unset($updateData['parent_id']);
                        } else {
                            // Validate parent exists
                            $parent = $this->getById($updateData['parent_id']);
                            if (!$parent) {
                                unset($updateData['parent_id']);
                            }
                        }
                    }
                }
            }
            
            return $this->db->update('categories', $updateData, 'id = :id', ['id' => $id]);
        } catch (\Exception $e) {
            error_log('Category update error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        try {
            // Check if category has products
            $productCount = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM products WHERE category_id = :id",
                ['id' => $id]
            )['count'] ?? 0;
            
            if ($productCount > 0) {
                return false; // Has products, cannot delete
            }
            
            // Check if category has children
            $children = $this->getChildren($id, false);
            if (!empty($children)) {
                return false; // Has sub-categories, cannot delete
            }
            
            return $this->db->delete('categories', 'id = :id', ['id' => $id]);
        } catch (\Exception $e) {
            error_log('Category delete error: ' . $e->getMessage());
            return false;
        }
    }

    public function getPath($categoryId, $separator = ' > ')
    {
        $ancestors = $this->getAncestors($categoryId);
        $category = $this->getById($categoryId);
        
        $path = [];
        foreach ($ancestors as $ancestor) {
            $path[] = $ancestor['name'];
        }
        if ($category) {
            $path[] = $category['name'];
        }
        
        return implode($separator, $path);
    }

    public function getBreadcrumbs($categoryId)
    {
        $breadcrumbs = [];
        $ancestors = $this->getAncestors($categoryId);
        $category = $this->getById($categoryId);
        
        $breadcrumbs[] = [
            'name' => 'Home',
            'url' => '/',
            'slug' => ''
        ];
        
        foreach ($ancestors as $ancestor) {
            $breadcrumbs[] = [
                'name' => $ancestor['name'],
                'url' => '/products.php?category=' . $ancestor['slug'],
                'slug' => $ancestor['slug']
            ];
        }
        
        if ($category) {
            $breadcrumbs[] = [
                'name' => $category['name'],
                'url' => '/products.php?category=' . $category['slug'],
                'slug' => $category['slug']
            ];
        }
        
        return $breadcrumbs;
    }

    public function getProductCount($categoryId, $includeSubcategories = true)
    {
        try {
            if ($includeSubcategories) {
                $descendants = $this->getDescendants($categoryId, true);
                $descendants[] = $categoryId;
                $placeholders = implode(',', array_fill(0, count($descendants), '?'));
                $sql = "SELECT COUNT(*) as count FROM products WHERE category_id IN ($placeholders)";
                $result = $this->db->fetchOne($sql, $descendants);
            } else {
                $result = $this->db->fetchOne(
                    "SELECT COUNT(*) as count FROM products WHERE category_id = :id",
                    ['id' => $categoryId]
                );
            }
            return $result['count'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function reorder($orderData)
    {
        try {
            $pdo = $this->db->getPdo();
            $pdo->beginTransaction();
            
            foreach ($orderData as $item) {
                $categoryId = (int)($item['id'] ?? 0);
                if ($categoryId <= 0) continue;
                
                $updateData = [
                    'sort_order' => (int)($item['sort_order'] ?? 0)
                ];
                
                // Handle parent_id - can be null, empty string, or integer
                if (isset($item['parent_id'])) {
                    $parentId = $item['parent_id'];
                    if ($parentId === '' || $parentId === null || $parentId === 'null' || $parentId === 0) {
                        $updateData['parent_id'] = null;
                    } else {
                        $parentId = (int)$parentId;
                        // Validate parent exists
                        if ($parentId > 0) {
                            $parent = $this->getById($parentId);
                            if ($parent && $parentId != $categoryId) {
                                // Prevent circular reference
                                $descendants = $this->getDescendants($categoryId, false);
                                if (!in_array($parentId, $descendants)) {
                                    $updateData['parent_id'] = $parentId;
                                } else {
                                    $updateData['parent_id'] = null; // Circular reference, set to null
                                }
                            } else {
                                $updateData['parent_id'] = null; // Invalid parent, set to null
                            }
                        } else {
                            $updateData['parent_id'] = null;
                        }
                    }
                }
                
                $this->db->update('categories', $updateData, 'id = :id', ['id' => $categoryId]);
            }
            
            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            error_log('Category reorder error: ' . $e->getMessage());
            return false;
        }
    }
}

