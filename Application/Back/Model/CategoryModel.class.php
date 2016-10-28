<?php
namespace Back\Model;

use Think\Model;

Class CategoryModel extends Model
{
	public function getTreeList()
	{
		S(['type'=>'Memcache', 'host'=>'127.0.0.1', 'port'=>'11211']);
		$key = 'back_category_tree';
		if(! ($tree = S($key))){
			$rows = $this->order('sort_number')->select();
			$tree = $this->tree($rows);

			S($key, $tree);
		}
		return $tree;
	}

	public function tree($rows, $category_id = 0, $deep = 0)
	{
		static $tree = [];
		foreach ($rows as $row) {
			if ($row['parent_id'] == $category_id) {
				$row['deep'] = $deep;
				$tree[] = $row;
				$this->tree($rows, $row['category_id'], $deep+1);
			}
		}
		return $tree;
	}
}